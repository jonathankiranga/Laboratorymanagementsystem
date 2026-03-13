<?php
require_once '../db_connection.php';
require_once '../functions/functions.php';
require_once '../functions/tasks.php';
require_once 'getrefferencesfunction.inc';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// --- Basic required inputs ---
$username = trim($_POST['username'] ?? '');
$user_id  = trim($_POST['user_id'] ?? '');

if ($username === '' || $user_id === '') {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Missing username or user_id.']);
    exit;
}

// keys folder and reading keys
$dir = __DIR__ . "/userkeys/$user_id";
$privateKeyPath = "$dir/private_key.pem";
$publicKeyPath  = "$dir/public_key.pem";

if (!file_exists($privateKeyPath)) {
    echo json_encode(['success' => false, 'message' => 'Error: Private key file does not exist.']);
    exit;
}
$privateKey = file_get_contents($privateKeyPath);
if ($privateKey === false) {
    echo json_encode(['success' => false, 'message' => 'Error: Unable to read private key file.']);
    exit;
}
$publicKey = file_exists($publicKeyPath) ? file_get_contents($publicKeyPath) : null;

// --- Required form fields validation (explicit keys) ---
$requiredKeys = [
    'date' => $_POST['date'] ?? null,
    'documentno' => $_POST['documentno'] ?? null,
    'CustomerName' => $_POST['CustomerName'] ?? null,
    'CustomerID' => $_POST['CustomerID'] ?? null,
    'sampledby' => $_POST['sampledby'] ?? null,
    'SamplingMethod' => $_POST['SamplingMethod'] ?? null,
    'samplingdate' => $_POST['samplingdate'] ?? null,
    'Orderno' => $_POST['Orderno'] ?? null
];

$missing = [];
foreach ($requiredKeys as $label => $val) {
    if ($val === null || trim((string)$val) === '') {
        $missing[] = $label;
    }
}

if (!empty($missing)) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
    exit;
}

// sanitize/assign top-level fields
$date           = $_POST['date'];
$documentNo     = $_POST['documentno'];
$customerName   = $_POST['CustomerName'];
$customerId     = $_POST['CustomerID'];
$sampledBy      = $_POST['sampledby'];
$samplingMethod = $_POST['SamplingMethod'];
$samplingDate   = $_POST['samplingdate'];
$orderNo        = $_POST['Orderno'];
$rowsposted     = (int) ($_POST['tablecount'] ?? 0);
$shouldSendSampleReceivedEmail = isset($_POST['send_sample_received_email']) && (string)$_POST['send_sample_received_email'] === '1';

// date validation (ensure sampling date is NOT later than registration date)
try {
    $registrationDate = new DateTime($date);
    $sampleDate = new DateTime($samplingDate);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
    exit;
}
if ($sampleDate > $registrationDate) { // sampleDate later than registration is invalid
    echo json_encode(['success' => false, 'message' => 'The sampling date cannot be later than the registration date.']);
    exit;
}

// ensure upload dir exists
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
        exit;
    }
}

// --- Prepare structures to store rows ---
$parameterData = []; // per row metadata that goes into sample_tests insert
$testData = [];      // nested test results data

// Accept uploaded file array if provided
$sampleFiles = $_FILES['sample_images'] ?? null;

// Loop over posted rows
for ($i = 0; $i < $rowsposted; $i++) {
    // generate image/sample ids and collect row fields (with guards)
    $imageNo      = GetTempBarcoderfNo(40, $i + 1);
    $sampletype   = isset($_POST['standard_id'][$i]) ? (int)$_POST['standard_id'][$i] : null;
    $matrix_id    = isset($_POST['matrix_id'][$i]) ? (int)$_POST['matrix_id'][$i] : null;
    $samplesCount = isset($_POST['samples'][$i]) ? (int)$_POST['samples'][$i] : 0;
    $sku          = $_POST['kit_units'][$i] ?? null;
    $batchNo      = $_POST['batch_no'][$i] ?? null;
    $batchSize    = $_POST['batch_size'][$i] ?? null;
    $mandate      = $_POST['date_mfg'][$i] ?? null;
    $expDate      = $_POST['date_exp'][$i] ?? null;
    $externalSample = $_POST['sample_source'][$i] ?? null;

    // file upload for this row (if provided)
    $uploadedFile = null;
    if ($sampleFiles && isset($sampleFiles['name'][$i]) && $sampleFiles['error'][$i] === UPLOAD_ERR_OK) {
        $originalFileName = $sampleFiles['name'][$i];
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $newFileName = $imageNo . '.' . $fileExtension;
        $destination = $uploadDir . $newFileName;
        if (move_uploaded_file($sampleFiles['tmp_name'][$i], $destination)) {
            $uploadedFile = $destination;
        }
    }

    $parameterData[$i] = [
        'imageno' => $imageNo,
        'sampleType' => $sampletype,
        'matrixID' => $matrix_id,
        'sku' => $sku,
        'batchNo' => $batchNo,
        'batchSize' => $batchSize,
        'mandate' => $mandate,
        'expDate' => $expDate,
        'externalSample' => $externalSample,
        'sampleFileKey' => $uploadedFile,
        'User_name' => $username
    ];

    // Determine parameters for this row
    $filteredParameters = [];
    if (!empty($matrix_id)) {
        $filteredParameters = gettestparameters($sampletype, $matrix_id);
    } else {
        $filteredParameters = gettestparametersALL($sampletype);
        // If front-end sent explicit selected_params for this row, prefer them
        if (isset($_POST['selected_params'][$i]) && is_array($_POST['selected_params'][$i])) {
            $paramsFromFrontend = [];
            foreach ($_POST['selected_params'][$i] as $rowParam) {
                // allow either associative or simple arrays from frontend
                $parameterId = isset($rowParam['parameterId']) ? (int)$rowParam['parameterId'] : (isset($rowParam['ParameterID']) ? (int)$rowParam['ParameterID'] : null);
                $standardId  = isset($rowParam['standardId']) ? $rowParam['standardId'] : (isset($rowParam['StandardID']) ? $rowParam['StandardID'] : null);
                $baseId      = isset($rowParam['baseId']) ? (int)$rowParam['baseId'] : (isset($rowParam['BaseID']) ? (int)$rowParam['BaseID'] : null);

                if ($parameterId !== null) {
                    $paramsFromFrontend[] = [
                        'ParameterID' => $parameterId,
                        'StandardID'  => $standardId,
                        'BaseID'      => $baseId
                    ];
                }
            }
            if (!empty($paramsFromFrontend)) {
                $filteredParameters = $paramsFromFrontend;
            }
        }
    }

    // Build sample tests rows and test results placeholders
    $testData[$i] = [];
    for ($smpcnt = 0; $smpcnt < max(1, $samplesCount); $smpcnt++) {
        $sampleNo = GetTempBarcoderfNo(19, $smpcnt + 1);
        foreach ($filteredParameters as $parameter) {
            // ensure keys exist
            $paramId = isset($parameter['ParameterID']) ? (int)$parameter['ParameterID'] : (isset($parameter['parameterId']) ? (int)$parameter['parameterId'] : null);
            $stdId   = $parameter['StandardID'] ?? $parameter['standardID'] ?? null;
            $baseId  = isset($parameter['BaseID']) ? (int)$parameter['BaseID'] : (isset($parameter['BasePID']) ? (int)$parameter['BasePID'] : null);

            if ($paramId === null) continue;

            $testData[$i][$smpcnt][] = [
                'parameterId' => $paramId,
                'sampleType'  => $stdId,
                'sampleNo'    => $sampleNo,
                'User_name'   => $username,
                'BasePID'     => $baseId
            ];
        }
    }
}

// --- Create blockchain entry (hash/sign/encrypt) ---
try {
    $lastBlockQuery = $conn->query("SELECT current_hash FROM blockchain_ledger ORDER BY block_id DESC LIMIT 1");
    $lastBlock = $lastBlockQuery ? $lastBlockQuery->fetch_assoc() : null;
    $previousHash = $lastBlock ? $lastBlock['current_hash'] : str_repeat("0", 64);

    // Merge header / parameter and a small stable string for hashing.
    $sampleHeader = [
        'date' => $date,
        'documentNo' => $documentNo,
        'customerName' => $customerName,
        'customerId' => $customerId,
        'sampledBy' => $sampledBy,
        'samplingMethod' => $samplingMethod,
        'samplingDate' => $samplingDate,
        'orderNo' => $orderNo,
        'User_name' => $username
    ];

    // For the purpose of creating a deterministic string to hash, serialize JSON
    $dataToHash = json_encode([
        'header' => $sampleHeader,
        'parameterData' => $parameterData,
        'testData' => $testData,
        'previousHash' => $previousHash
    ], JSON_UNESCAPED_UNICODE);

    $current_hash = hash('sha256', $dataToHash);
    $digital_signature = signData($current_hash, $privateKey);
    $encryptdata = encryptPrivateKey($dataToHash, $privateKey);

    // Start transaction
    $conn->autocommit(false);

    // Insert into blockchain_ledger
    $stmt = $conn->prepare("INSERT INTO `blockchain_ledger` (`timestamp`, `previous_hash`, `current_hash`, `digital_signature`, `encrypted_data`, `status`, `userid`) VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?, 'active', ?)");
    if (!$stmt) throw new Exception("Prepare blockchain insert failed: " . $conn->error);
    $stmt->bind_param("sssss", $previousHash, $current_hash, $digital_signature, $encryptdata, $user_id);
    $stmt->execute();
    $blockId = $conn->insert_id;
    $stmt->close();

    // Now insert into sample_header
    $documentNo = GetNextLabrefNo('10'); // looks like you reset documentNo here intentionally
    $stmt = $conn->prepare("INSERT INTO sample_header (Date, DocumentNo, CustomerName, CustomerID, SampledBy, SamplingMethod, SamplingDate, OrderNo, User_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare statement sample_header failed: " . $conn->error);
    $stmt->bind_param("sssssssss", $date, $documentNo, $customerName, $customerId, $sampledBy, $samplingMethod, $samplingDate, $orderNo, $username);
    if (!$stmt->execute()) throw new Exception("Execute sample_header failed: " . $stmt->error);
    $HeaderID = $conn->insert_id;
    $stmt->close();

    // Log blockchain linkage
    log_transaction_metadata($conn, $blockId, $HeaderID, 'sample_header');

    // Prepare sample_tests insert
    $stmt = $conn->prepare("INSERT INTO `sample_tests` (`HeaderID`,`SampleID`,`StandardID`,`SampleFileKey`,`SKU`,`BatchNo`,`BatchSize`,`ManufactureDate`,`ExpDate`,`ExternalSample`,`User_name`,`BaseID`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) throw new Exception("Prepare sample_tests failed: " . $conn->error);

    // Prepare test_results insert
    $teststmt = $conn->prepare("INSERT INTO `test_results` (`TestID`,`HeaderID`,`SampleID`,`StandardID`,`ParameterID`,`User_name`,`StatusID`,`BaseID`) VALUES (?,?,?,?,?,?,1,?)");
    if (!$teststmt) throw new Exception("Prepare test_results failed: " . $conn->error);

    // Loop through parameterData and testData and insert
    foreach ($parameterData as $id => $datarows) {
        $SampleID_for_tests = GetNextLabrefNo(40); // your original used GetNextLabrefNo(40) here
        // Bind and execute sample_tests
        $stmt->bind_param(
            "isisssissssi",
            $HeaderID,
            $datarows['imageno'],
            $datarows['sampleType'],
            $datarows['sampleFileKey'],
            $datarows['sku'],
            $datarows['batchNo'],
            $datarows['batchSize'],
            $datarows['mandate'],
            $datarows['expDate'],
            $datarows['externalSample'],
            $datarows['User_name'],
            $datarows['matrixID']
        );
        if (!$stmt->execute()) {
            throw new Exception("Error executing sample_tests: " . $stmt->error);
        }
        $TestID = $conn->insert_id;

        // For every sample instance (smpcnt) in this row
        if (isset($testData[$id]) && is_array($testData[$id])) {
            foreach ($testData[$id] as $smpcnt => $collection) {
                 $SampleID = GetNextLabrefNo('19');
               
                 foreach ($collection as $tedata) {
                    // Guard fields
                    $std = $tedata['sampleType'] ?? null;
                    $param = $tedata['parameterId'] ?? null;
                    $user_here = $tedata['User_name'] ?? $username;
                    $basepid = $tedata['BasePID'] ?? null;

                    $teststmt->bind_param("iisiisi", $TestID, $HeaderID, $SampleID, $std, $param, $user_here, $basepid);
                    if (!$teststmt->execute()) {
                        throw new Exception("Error executing test_results: " . $teststmt->error);
                    }
                }
            }
        }
    }

    $teststmt->close();
    $stmt->close();

    // Always track user's email decision/action
    // Use SUCCESS for skip so this remains compatible with strict enum schemas.
    $emailLogStatus = 'SUCCESS';
    $emailLogError = 'Email skipped by user selection.';

    if ($shouldSendSampleReceivedEmail) {
        $emailLogStatus = 'FAILED';
        $emailLogError = null;

        if (!class_exists('EventManager')) {
            $emailLogError = 'Event manager unavailable.';
        } else {
            $customerEmail = null;
            $emailStmt = $conn->prepare("SELECT email FROM debtors WHERE itemcode = ? LIMIT 1");
            if ($emailStmt) {
                $emailStmt->bind_param("s", $customerId);
                $emailStmt->execute();
                $emailRes = $emailStmt->get_result();
                if ($emailRes && ($emailRow = $emailRes->fetch_assoc())) {
                    $candidateEmail = trim((string)($emailRow['email'] ?? ''));
                    if ($candidateEmail !== '') {
                        $customerEmail = $candidateEmail;
                    }
                }
                $emailStmt->close();
            }

            if ($customerEmail !== null) {
                $Alertdata = ['test_id' => $documentNo, 'customer_id' => $customerId];
                try {
                    $eventManager = new EventManager();
                    $eventManager->trigger_event('sample_received', $Alertdata);
                    $emailLogStatus = 'SUCCESS';
                } catch (Throwable $emailEx) {
                    $emailLogStatus = 'FAILED';
                    $emailLogError = 'Email trigger error: ' . $emailEx->getMessage();
                }
            } else {
                $emailLogStatus = 'FAILED';
                $emailLogError = 'Customer email not found.';
            }
        }
    } else {
        // Also record skipped choice in event logs so it is visible in alert reports
        if (class_exists('EventManager')) {
            try {
                $eventManager = new EventManager();
                $eventManager->log_event_decision(
                    'sample_received',
                    $documentNo,
                    'success',
                    'Email not sent because user selected "Skip Email".'
                );
            } catch (Throwable $skipLogEx) {
                // keep registration successful even if secondary logging fails
            }
        }
    }

    // Track email action outcome in audit_log for both send and skip choices
    logAction($conn, 'EMAIL_SAMPLE_RECEIVED', $documentNo, $current_hash, $user_id, $emailLogStatus, $emailLogError);
    logAction($conn, 'INSERT', $documentNo, $current_hash, $user_id, 'SUCCESS');

    $conn->commit();
    $conn->autocommit(true);

    echo json_encode([
        'success' => true,
        'message' => "Sample Registration no: $documentNo Successful",
        'email_status' => $emailLogStatus,
        'email_note' => $emailLogError
    ]);
    exit;
} catch (Exception $e) {
    // rollback, log and return error
    if ($conn) {
        $conn->rollback();
        $conn->autocommit(true);
    }
    logAction($conn, 'INSERT', $documentNo ?? '', $current_hash ?? '', $user_id ?? '', 'FAILED', $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
