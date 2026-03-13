<?php

require '../db_connection.php'; // mysqli connection $conn

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $response = [
        'success' => false,
        'message' => 'Invalid request'
    ];

    // Get POST data safely
    $ParameterID  = isset($_POST['ParameterIDForm']) ? intval($_POST['ParameterIDForm']) : null;
    $name         = trim($_POST['ParameterNameForm']);
    $NeutralityID = intval($_POST['neutralityID']);
    $TdsID        = intval($_POST['tdsID']);
    $updatedAt    = date('Y-m-d H:i:s');
    $createdAt    = date('Y-m-d H:i:s');

    // Validate required field
    if (empty($name)) {
        $response['message'] = 'Parameter name is required.';
        echo json_encode($response);
        exit;
    }

    try {

        // ==========================================
        // UPDATE MODE
        // ==========================================
        if ($ParameterID) {

            // Check if another record already has this name
            $checkQuery = "SELECT ParameterID FROM BaseParameters 
                           WHERE ParameterName = ? AND ParameterID != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("si", $name, $ParameterID);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $response['message'] = 'Parameter name already exists.';
                $checkStmt->close();
                echo json_encode($response);
                exit;
            }
            $checkStmt->close();

            // Proceed with update
            $query = "UPDATE BaseParameters SET 
                        ParameterName = ?,
                        NeutralityID = ?,
                        TdsID = ?,
                        UpdatedAt = ?
                      WHERE ParameterID = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("siisi", $name, $NeutralityID, $TdsID, $updatedAt, $ParameterID);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Parameter updated successfully.';
            } else {
                $response['message'] = $stmt->error;
            }

            $stmt->close();

        }
        // ==========================================
        // CREATE MODE
        // ==========================================
        else {

            // Check if parameter already exists
            $checkQuery = "SELECT ParameterID FROM BaseParameters WHERE ParameterName = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {

                // Already exists
                $checkStmt->bind_result($existingID);
                $checkStmt->fetch();

                $response['success'] = true;
                $response['message'] = 'Parameter already exists.';
                $response['ParameterID'] = $existingID;

                $checkStmt->close();
                echo json_encode($response);
                exit;
            }

            $checkStmt->close();

            // Insert new parameter
            $insertQuery = "INSERT INTO BaseParameters 
                            (ParameterName, NeutralityID, TdsID, CreatedAt) 
                            VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("siis", $name, $NeutralityID, $TdsID, $createdAt);

            if ($stmt->execute()) {

                $response['success'] = true;
                $response['message'] = 'Parameter created successfully.';
                $response['ParameterID'] = $stmt->insert_id;

            } else {
                $response['message'] = $stmt->error;
            }

            $stmt->close();
        }

    } catch (Exception $e) {

        $response['message'] = $e->getMessage();

    }

    echo json_encode($response);
}
?>
