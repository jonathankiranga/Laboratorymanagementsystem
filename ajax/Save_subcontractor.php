<?php
// Database connection
require '../db_connection.php'; // Ensure this connects to your MySQLi instance
// Sanitize and fetch POST data
$customer = $conn->real_escape_string($_POST['customer']);
$company = $conn->real_escape_string($_POST['company']);
$postcode = $conn->real_escape_string($_POST['postcode']);
$city = $conn->real_escape_string($_POST['city']);
$country = $conn->real_escape_string($_POST['country']);
$phone = $conn->real_escape_string($_POST['phone']);
$altcontact = $conn->real_escape_string($_POST['altcontact']);
$email = $conn->real_escape_string($_POST['email']);
$inactive = $conn->real_escape_string($_POST['inactive']);


if(isset($_POST['delete'])){
    $lastBlockQuery = $conn->query("SELECT COUNT(*) as accounts FROM test_assignments where subcontractor='$itemcode'");
    $lastBlock   = $lastBlockQuery->fetch_assoc();
    $counts = $lastBlock['accounts'] ;
    if($counts==0){
     
    $itemcode=$_POST['itemcode'];
    $sql = "delete from subcontractors  where id= '$itemcode'";
    }
}else{
    if(isset($_POST['itemcode'])){
        $itemcode=$_POST['itemcode'];
        $sql = "update subcontractors set name='$customer', address='$company', address2='$postcode', city='$city',"
            . " country='$country', phone='$phone', alt_contact='$altcontact', email='$email',"
            . " inactive='$inactive' where id= '$itemcode'";
   }else{
    // Insert query
    $sql = "INSERT INTO subcontractors (name, address, address2, city, country, phone, alt_contact, email, inactive) 
            VALUES ('$customer', '$company', '$postcode', '$city', '$country', '$phone', '$altcontact', '$email', '$inactive')";
    }
}




// Execute the statement
if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => ' successful']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error : ' . $conn->error]);
}
$conn->close();
?>
