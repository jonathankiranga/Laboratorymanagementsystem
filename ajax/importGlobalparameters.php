<?php
require '../db_connection.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];
    $fileName = $_FILES['excelFile']['name'];
    $errormessage = []; // Initialize error messages
    $insertCount = 0;
    
    $sql = "INSERT INTO BaseParameters (ParameterName) VALUES (?) "
            . " ON DUPLICATE KEY UPDATE  ParameterName = VALUES(ParameterName)";
             
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $errormessage[] = ["success" => false, "message" => $conn->error];
        return;
    }
            
     $importdata[];       
    try {
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $index => $row) {
            if ($index === 1) continue; // Skip header row
                $mykey=trim($row[0]);
                $importdata[$mykey] = sprintf('%s',$mykey);
        }
        
        foreach ($importdata as $value) {
            $stmt->bind_param('s',$value);
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
?>
