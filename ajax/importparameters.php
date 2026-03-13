<?php
require '../db_connection.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
$cacheStandards=getallparams();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];
    $fileName = $_FILES['excelFile']['name'];
    $standardID = intval($_POST['StandardID']); // Ensure StandardID is an integer
    $errormessage = []; // Initialize error messages
    $insertCount = 0;

    try {
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $index => $row) {
            if ($index === 1) continue; // Skip header row

            $sql = "INSERT INTO testparameters (ParameterName, StandardID, MinLimit, MaxLimit, Method, Vital, Category, MRL, MRLUnit, UnitOfMeasure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $errormessage[] = ["success" => false, "message" => $conn->error];
                continue;
            }
           

            $stmt->bind_param(
                'siddsisdss',
                sprintf("%s",trim($row['A'])),
                $standardID,
                sprintf("%s",trim($row['B'])),
                sprintf("%s",trim($row['C'])),
                sprintf("%s",trim($row['D'])),
                sprintf("%s",trim($row['E'])),
                sprintf("%s",trim($row['F'])),
                sprintf("%s",trim($row['G'])),
                sprintf("%s",trim($row['H'])),
                sprintf("%s",trim($row['I']))
            );
 
             $paradesc = trim($row['A']);

                if (!isset($cacheStandards[$paradesc])) {

                    $stmtb = $conn->prepare("
                        INSERT INTO baseparameters (ParameterName)
                        VALUES (?)
                        ON DUPLICATE KEY UPDATE ParameterID = LAST_INSERT_ID(ParameterID)
                    ");

                    $stmtb->bind_param("s", $paradesc);
                    $stmtb->execute();
                    $stmtb->close();

                    $cacheStandards[$paradesc] = true;
                }

             
            if ($stmt->execute()) {
                $insertCount++;
            } else {
                $errormessage[] = ["success" => false, "message" => $stmt->error];
            }
        }

        echo json_encode([
            "success" => $insertCount > 0,
            "message" => $insertCount > 0 ? "$insertCount parameters uploaded successfully." : "No data uploaded or errors occurred.",
            "errors" => $errormessage
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error processing the file: " . $e->getMessage()
        ]);
        exit;
    }
    
    
} else {
    echo json_encode(["success" => false, "message" => "No file uploaded or invalid request."]);
    exit;
}


function getallparams(){
    global  $conn;
    
       $sql="SELECT ParameterName FROM baseparameters
            UNION
            SELECT ParameterName FROM testparameters;
            ";
       $stmt = $conn->query($sql);
         $response = [];
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                 $trimedvalue= trim($row['ParameterName']);
                 $response[$trimedvalue] = $row['ParameterName'];
            }
        }
        $stmt->close();
        
    return $response;
}

/*INSERT INTO baseparameters (ParameterName)
SELECT DISTINCT ParameterName
FROM testparameters
WHERE ParameterName IS NOT NULL
AND ParameterName NOT IN (
    SELECT ParameterName FROM baseparameters
);
*/

/*UPDATE testparameters t
JOIN baseparameters b 
ON t.ParameterName = b.ParameterName
SET t.BaseID = b.ParameterID;
*/

?>
