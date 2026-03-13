<?php
require '../db_connection.php';

$resultsID = $_GET['resultsID'];
$stmt = $conn->prepare("SELECT tr.*,tp.*,ts.* FROM test_results tr 
join testparameters tp on tp.ParameterID=tr.ParameterID and tp.StandardID=tr.StandardID
join teststandards ts on ts.StandardID=tr.StandardID WHERE tr.resultsID = ?");
$stmt->bind_param("i", $resultsID);
$stmt->execute();
$result = $stmt->get_result();

 header('Content-Type: application/json');
if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'result' => $result->fetch_assoc()]);
} else {
    echo json_encode(['success' => false, 'message' => 'No result found.']);
}
