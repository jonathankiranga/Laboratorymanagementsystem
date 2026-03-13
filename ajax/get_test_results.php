<?php
require '../db_connection.php';

$statusID = $_GET['statusID'];
 
$stmt = $conn->prepare("SELECT Distinct st.*,sp.*, tr.*,tp.*,ts.* FROM test_results tr 
JOIN Sample_Tests st ON tr.TestID = st.TestID
JOIN Sample_Header sp ON tr.HeaderID = sp.HeaderID
join testparameters tp on tp.ParameterID=tr.ParameterID and tp.StandardID=tr.StandardID
join teststandards ts on ts.StandardID=tr.StandardID WHERE tr.StatusID = ?");
$stmt->bind_param("i", $statusID);
$stmt->execute();
$result = $stmt->get_result();

$response = ['success' => false, 'results' => []];
if ($result->num_rows > 0) {
    $response['success'] = true;
    while ($row = $result->fetch_assoc()) {
        $response['results'][] = $row;
    }
}

 header('Content-Type: application/json');
echo json_encode($response);
