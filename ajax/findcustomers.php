<?php
// Database connection
require '../db_connection.php'; // Ensure this connects to your PDO instance and sets $pdo

header('Content-Type: application/json');
$return = json_encode(['success' => false]);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itemcode'])) {
    $itemcode = $_POST['itemcode'];
    
    $query = $conn->prepare("SELECT * FROM debtors where itemcode = ? ");
    $query->bind_param("s",$itemcode);
    $query->execute();
    $result = $query->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $return = json_encode([ 'data' => $data,  'success' => true]);

}

echo $return;
?>


