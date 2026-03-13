<?php
// Database connection
require '../db_connection.php'; // Ensure this connects to your PDO instance and sets $pdo

$records_per_page = 50; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;
 // Fetch total count of records
$total_records_result = $conn->query("SELECT COUNT(*) AS count FROM subcontractors");
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);
// Fetch paginated records
$query = $conn->prepare("SELECT * FROM subcontractors  LIMIT ?, ?");
$query->bind_param('ii', $offset, $records_per_page);
$query->execute();
$result = $query->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode([
    'data' => $data,
    'current_page' => $page,
    'total_pages' => $total_pages,
]);

?>


