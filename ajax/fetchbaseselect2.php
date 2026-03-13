<?php
// Database connection
require '../db_connection.php'; 
// Ensure this connects to your PDO instance and sets $pdo
// Retrieve input values
$page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit      = 10; // records per page
$offset     = ($page - 1) * $limit;



$where = '';
$bindTypes = '';
$bindValues = [];

if ($searchTerm !== '') {
    $where = "WHERE ParameterName LIKE ? ";
    $searchPattern = '%' . $searchTerm . '%';
    $bindTypes = 's';
    $bindValues = [$searchPattern];
}

// Build the SQL query for counting total records
$countSql = "SELECT COUNT(*) FROM BaseParameters $where";
$stmt = $conn->prepare($countSql);

// Bind parameters if a search term was provided
if ($searchTerm !== '') {
    $stmt->bind_param($bindTypes, ...$bindValues);
}

// Execute the statement
$stmt->execute();
$stmt->bind_result($totalRecords);
$stmt->fetch();
$stmt->close();
$totalPages = ceil($totalRecords / $limit);


$where      = '';
$bindTypes  = '';
$bindValues = [];
if ($searchTerm !== '') {
    $where = "WHERE  ts.ParameterName LIKE ? ";
    $searchPattern = '%' . $searchTerm . '%';
    $bindTypes = 'sii';
    $bindValues = [$searchPattern, $offset,$limit];
}else{
    $bindTypes = 'ii';
    $bindValues = [$offset,$limit];
}



// Fetch paginated records
$query = $conn->prepare("SELECT  ts.*  FROM BaseParameters ts  $where LIMIT ?, ?");
$query->bind_param($bindTypes, ...$bindValues);
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
    'total_pages' => $totalPages,
]);

?>


