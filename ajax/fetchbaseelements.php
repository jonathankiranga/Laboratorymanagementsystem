<?php
// Database connection
require '../db_connection.php'; 
// Ensure this connects to your PDO instance and sets $pdo
// Retrieve input values
$page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit      = 10; // records per page
$offset     = ($page - 1) * $limit;

$NEUTRALITY = [
        ["id" => "1","ion_name" => "Na⁺", "type" => "Cation", "weight" => 22.99, "charge" => 1],
        ["id" => "2","ion_name" => "K⁺", "type" => "Cation", "weight" => 39.10, "charge" => 1],
        ["id" => "3","ion_name" => "Ca²⁺", "type" => "Cation", "weight" => 40.08, "charge" => 2],
        ["id" => "4","ion_name" => "Mg²⁺","type" => "Cation", "weight" => 24.31, "charge" => 2],
        ["id" => "5","ion_name" => "NH₄⁺", "type" => "Cation", "weight" => 18.04, "charge" => 1],
        ["id" => "6","ion_name" => "Cl⁻", "type" => "Anion",  "weight" => 35.45, "charge" => -1],
        ["id" => "7","ion_name" => "HCO₃⁻", "type" => "Anion",  "weight" => 61.02, "charge" => -1],
        ["id" => "8","ion_name" => "NO₃⁻","type" => "Anion",  "weight" => 62.00, "charge" => -1],
        ["id" => "9","ion_name" => "SO₄²⁻", "type" => "Anion",  "weight" => 96.06, "charge" => -2],
        ["id" => "10","ion_name" => "CO₃²⁻", "type" => "Anion",  "weight" => 60.01, "charge" => -2]
    ];

$findn=array();
foreach ($NEUTRALITY as $value) {
    $findn[$value['id']] = $value['ion_name'];
}

$TDS = [
    ["id" => "1",'Element' => 'Hydrogen', 'Symbol' => 'H', 'AtomicWeight' => 1.008],
    ["id" => "2",'Element' => 'Oxygen', 'Symbol' => 'O', 'AtomicWeight' => 16.00],
    ["id" => "3",'Element' => 'Nitrogen', 'Symbol' => 'N', 'AtomicWeight' => 14.01],
    ["id" => "4",'Element' => 'Carbon', 'Symbol' => 'C', 'AtomicWeight' => 12.01],
    ["id" => "5",'Element' => 'Sodium', 'Symbol' => 'Na', 'AtomicWeight' => 22.99],
    ["id" => "6",'Element' => 'Potassium', 'Symbol' => 'K', 'AtomicWeight' => 39.10],
    ["id" => "7",'Element' => 'Calcium', 'Symbol' => 'Ca', 'AtomicWeight' => 40.08],
    ["id" => "8",'Element' => 'Magnesium', 'Symbol' => 'Mg', 'AtomicWeight' => 24.31],
    ["id" => "9",'Element' => 'Iron', 'Symbol' => 'Fe', 'AtomicWeight' => 55.85],
    ["id" => "10",'Element' => 'Copper', 'Symbol' => 'Cu', 'AtomicWeight' => 63.55],
    ["id" => "11",'Element' => 'Lead', 'Symbol' => 'Pb', 'AtomicWeight' => 207.2],
    ["id" => "12",'Element' => 'Zinc', 'Symbol' => 'Zn', 'AtomicWeight' => 65.38],
    ["id" => "13",'Element' => 'Manganese', 'Symbol' => 'Mn', 'AtomicWeight' => 54.94],
    ["id" => "14",'Element' => 'Chlorine', 'Symbol' => 'Cl', 'AtomicWeight' => 35.45],
    ["id" => "15",'Element' => 'Fluorine', 'Symbol' => 'F', 'AtomicWeight' => 19.00],
    ["id" => "16",'Element' => 'Boron', 'Symbol' => 'B', 'AtomicWeight' => 10.81],
    ["id" => "17",'Element' => 'Sulfur', 'Symbol' => 'S', 'AtomicWeight' => 32.07],
    ["id" => "18",'Element' => 'Phosphorus', 'Symbol' => 'P', 'AtomicWeight' => 30.97],
];

$findt=array();
foreach ($TDS as $value) {
    $findt[$value['id']] = $value['Element'];
}

$where = '';
$bindTypes = '';
$bindValues = [];

if ($searchTerm !== '') {
    $where = "WHERE ParameterID  LIKE ? OR ParameterName LIKE ? ";
    $searchPattern = '%' . $searchTerm . '%';
    $bindTypes = 'ss';
    $bindValues = [$searchPattern, $searchPattern];
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
    $where = "WHERE ts.ParameterID LIKE ?  OR ts.ParameterName LIKE ? ";
    $searchPattern = '%' . $searchTerm . '%';
    $bindTypes = 'ssii';
    $bindValues = [$searchPattern,  $searchPattern, $offset,$limit];
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
           $nid=trim($row['NeutralityID']) ;
           $tid=trim($row['TdsID']) ;   
           $row['Neutrality'] = isset($findn[$nid])? $findn[$nid]:'';
           $row['Tds'] =isset($findt[$tid])?$findt[$tid]:''  ;
           $data[] = $row;
}


header('Content-Type: application/json');
echo json_encode([
    'data' => $data,
    'current_page' => $page,
    'total_pages' => $totalPages,
]);

?>


