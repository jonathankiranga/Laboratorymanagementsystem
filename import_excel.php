<?php
$config = include('include/config.php'); // Load the config file
// Database connection
$secretKey = $config['SECRET_KEY'];  // Access the SECRET_KEY from config
$db_host   = $config['DB_HOST'];
$db_name   = $config['DB_NAME'];
$db_username = $config['DB_USERNAME'];
$db_password = $config['DB_PASSWORD'];

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// File: import_excel.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $fileType = $_FILES['excel_file']['type'];
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];

    if (!in_array($fileType, $allowedTypes)) {
        die("Invalid file type. Please upload an Excel file.");
    }

    // Load the Excel file using PHPSpreadsheet
    $spreadsheet = IOFactory::load($fileTmpPath);
    $importType = $_POST['import_type'];

    if ($importType === 'separate_sheets') {
        // Read data from both sheets
        $testStandardsSheet = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
        $testParametersSheet = $spreadsheet->getSheet(1)->toArray(null, true, true, true);

        // Call functions to process data
        $conn->begin_transaction();
        try {
            importTestStandards($testStandardsSheet);
            importTestParameters($testParametersSheet);
            $conn->commit();
            echo "Data imported successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Failed: " . $e->getMessage();
        }
    } elseif ($importType === 'combined_sheet') {
        $combinedSheet = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
        $conn->begin_transaction();
        try {
            importCombinedSheet($combinedSheet);
            $conn->commit();
            echo "Data imported successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Failed: " . $e->getMessage();
        }
    }
}

function importTestStandards($data) {
    global $conn;
    array_shift($data); // Skip header row

    foreach ($data as $row) {
        $standardCode = $conn->real_escape_string($row['A']);
        $standardName = $conn->real_escape_string($row['B']);
        $description = $conn->real_escape_string($row['C']);
        $applicableRegulation = $conn->real_escape_string($row['D']);
        $sm = (int)$row['E'];

        $query = "
            INSERT INTO TestStandards (StandardCode, StandardName, Description, ApplicableRegulation, sm)
            VALUES ('$standardCode', '$standardName', '$description', '$applicableRegulation', $sm)
            ON DUPLICATE KEY UPDATE
                StandardName = VALUES(StandardName),
                Description = VALUES(Description),
                ApplicableRegulation = VALUES(ApplicableRegulation),
                sm = VALUES(sm)";
        if (!$conn->query($query)) {
            throw new Exception("Error inserting/updating TestStandards: " . $conn->error);
        }
    }
}

function importTestParameters($data) {
    global $conn;
    array_shift($data); // Skip header row

    foreach ($data as $row) {
        $parameterName = $conn->real_escape_string($row['A']);
        $standardCode = $conn->real_escape_string($row['B']);
        $limits = $conn->real_escape_string($row['C']);
        $minLimit = is_numeric($row['D']) ? $row['D'] : 'NULL';
        $maxLimit = is_numeric($row['E']) ? $row['E'] : 'NULL';
        $method = $conn->real_escape_string($row['F']);
        $vital = (int)$row['G'];
        $category = $conn->real_escape_string($row['H']);
        $mrl = is_numeric($row['I']) ? $row['I'] : 'NULL';
        $mrlUnit = $conn->real_escape_string($row['J']);
        $unitOfMeasure = $conn->real_escape_string($row['K']);

        $standardIDQuery = "SELECT StandardID FROM TestStandards WHERE StandardCode = '$standardCode'";
        $result = $conn->query($standardIDQuery);
        if ($result && $result->num_rows > 0) {
            $standardID = $result->fetch_assoc()['StandardID'];

            $query = "
                INSERT INTO TestParameters (ParameterName, StandardID, Limits, MinLimit, MaxLimit, Method, Vital, Category, MRL, MRLUnit, UnitOfMeasure)
                VALUES ('$parameterName', $standardID, '$limits', $minLimit, $maxLimit, '$method', $vital, '$category', $mrl, '$mrlUnit', '$unitOfMeasure')
                ON DUPLICATE KEY UPDATE
                    Limits = VALUES(Limits),
                    MinLimit = VALUES(MinLimit),
                    MaxLimit = VALUES(MaxLimit),
                    Method = VALUES(Method),
                    Vital = VALUES(Vital),
                    Category = VALUES(Category),
                    MRL = VALUES(MRL),
                    MRLUnit = VALUES(MRLUnit),
                    UnitOfMeasure = VALUES(UnitOfMeasure)";
            if (!$conn->query($query)) {
                throw new Exception("Error inserting/updating TestParameters: " . $conn->error);
            }
        }
    }
}

function importCombinedSheet($data) {
    global $conn;
    $standardsMap = [];
    $currentStandardCode = null;
    array_shift($data); // Skip header row

    foreach ($data as $row) {
        $standardName = trim($row['A']);
        $description = trim($row['B']);
        $applicableRegulation = trim($row['C']);
        $parameterName = trim($row['D']);

        if (!empty($standardName)) {
            $currentStandardCode = 'STD' . str_pad((count($standardsMap) + 1), 3, '0', STR_PAD_LEFT);
            $standardsMap[$standardName] = $currentStandardCode;

            $query = "
                INSERT INTO TestStandards (StandardCode, StandardName, Description, ApplicableRegulation)
                VALUES ('$currentStandardCode', '$standardName', '$description', '$applicableRegulation')
            ";
            if (!$conn->query($query)) {
                throw new Exception("Error inserting TestStandards: " . $conn->error);
            }
        }

        if (!empty($parameterName)) {
            $limits = trim($row['E']);
            $minLimit = is_numeric($row['F']) ? $row['F'] : 'NULL';
            $maxLimit = is_numeric($row['G']) ? $row['G'] : 'NULL';
            $method = trim($row['H']);
            $vital = (int)$row['I'];
            $category = trim($row['J']);
            $mrl = is_numeric($row['K']) ? $row['K'] : 'NULL';
            $mrlUnit = trim($row['L']);
            $unitOfMeasure = trim($row['M']);

            $query = "
                INSERT INTO TestParameters (ParameterName, StandardID, Limits, MinLimit, MaxLimit, Method, Vital, Category, MRL, MRLUnit, UnitOfMeasure)
                VALUES ('$parameterName', 
                        (SELECT StandardID FROM TestStandards WHERE StandardCode = '$currentStandardCode'),
                        '$limits', 
                        $minLimit, 
                        $maxLimit, 
                        '$method', 
                        $vital, 
                        '$category', 
                        $mrl, 
                        '$mrlUnit', 
                        '$unitOfMeasure')
            ";
            if (!$conn->query($query)) {
                throw new Exception("Error inserting TestParameters: " . $conn->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel Data</title>
    
</head>
<body>
    <div class="container mt-5">
        <h2>Import Test Standards and Parameters from Excel</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="excelFile" class="form-label">Select Excel File:</label>
                <input type="file" name="excel_file" id="excelFile" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="importType" class="form-label">Select Import Type:</label>
                <select name="import_type" id="importType" class="form-select" required>
                    <option value="separate_sheets">Separate Sheets (Standards & Parameters)</option>
                    <option value="combined_sheet">Combined Sheet</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Upload and Import</button>
        </form>
        <h3>Excel Layout Formats</h3>
    <h4>Separate Sheets:</h4>
    <p><strong>Sheet 1: Test Standards</strong></p>
    <ul>
        <li><strong>Column A:</strong> Standard Code</li>
        <li><strong>Column B:</strong> Standard Name</li>
        <li><strong>Column C:</strong> Description</li>
        <li><strong>Column D:</strong> Applicable Regulation</li>
        <li><strong>Column E:</strong> SM (blank)</li>
    </ul>
    <p><strong>Sheet 2: Test Parameters</strong></p>
    <ul>
        <li><strong>Column A:</strong> Parameter Name</li>
        <li><strong>Column B:</strong> Standard Code</li>
        <li><strong>Column C:</strong> Limits</li>
        <li><strong>Column D:</strong> Min Limit</li>
        <li><strong>Column E:</strong> Max Limit</li>
        <li><strong>Column F:</strong> Method</li>
        <li><strong>Column G:</strong> Vital (1 or 0)</li>
        <li><strong>Column H:</strong> Category('chemical',  'microbiological')</li>
        <li><strong>Column I:</strong> MRL</li>
        <li><strong>Column J:</strong> MRL Unit</li>
        <li><strong>Column K:</strong> Unit of Measure</li>
    </ul>

    <h4>Combined Sheet:</h4>
    <ul>
        <li><strong>Column A:</strong> Standard Name</li>
        <li><strong>Column B:</strong> Description</li>
        <li><strong>Column C:</strong> Applicable Regulation</li>
        <li><strong>Column D:</strong> Parameter Name</li>
        <li><strong>Column E:</strong> Limits(leave blank)</li>
        <li><strong>Column F:</strong> Min Limit</li>
        <li><strong>Column G:</strong> Max Limit</li>
        <li><strong>Column H:</strong> Method</li>
        <li><strong>Column I:</strong> Vital (1 or 0)</li>
        <li><strong>Column J:</strong> Category('chemical','microbiological')</li>
        <li><strong>Column K:</strong> MRL</li>
        <li><strong>Column L:</strong> MRL Unit(leave blank,select option)</li>
        <li><strong>Column M:</strong> Unit of Measure(leave blank,select option)</li>
    </ul>
    </div>
</body>
</html>
