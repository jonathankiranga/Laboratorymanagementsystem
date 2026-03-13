<?php
require '../db_connection.php'; // Ensure this connects to your MySQLi instance

function Triger_debtors($descript) {
    global $conn; // Make sure to use the global connection variable

    // Execute the query to count the number of debtors
    $result = $conn->query('SELECT COUNT(*) as count FROM debtors');

    // Check if the query was successful
    if (!$result) {
        die('Query failed: ' . $conn->error);
    }

    // Fetch the count
    $row = $result->fetch_assoc();
    $int = (int)$row['count']; // Get the count from the result

    // Pad the count with leading zeros to ensure it's 4 digits
    $pad_length = 4 - strlen($int);
    $replicate = str_repeat('0', max(0, $pad_length));
    $code = 'Dr' . substr($descript, 0, 2) . $replicate . $int;

    return $code; // Return the generated code
}


if(isset($_POST['deleteaccount'])){
   $itemcode= $_POST['itemcode'];
   //sample_header ( Date, DocumentNo, CustomerName,CustomerID
    $lastBlockQuery = $conn->query("SELECT COUNT(*) as accounts FROM sample_header where CustomerID='$itemcode'");
    $lastBlock = $lastBlockQuery->fetch_assoc();
    $counts = $lastBlock['accounts'] ;
    if($counts==0){
           // Prepare the SQL statement using prepared statements to prevent SQL injection
          $sql = "delete from `debtors`  where itemcode=? ";
          $stmt = $conn->prepare($sql);
             if ($stmt === false) {
                 die('Prepare failed: ' . $conn->error);
             }

         // Bind parameters
         $stmt->bind_param('s',$_POST['itemcode']);
         // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting customer: ' . $stmt->error]);
        }
    }
   
}else{
    if(isset($_POST['itemcode'])){

    // Prepare the SQL statement using prepared statements to prevent SQL injection
    $sql = "update `debtors`  set  contact= ?,  creditlimit=?, customer= ?, middlen=?, 
        phone=?, fax=?, company=?,  altcontact=?, email=?, city=?, country=?, inactive=?,
        postcode=?, curr_cod=?, customerposting=?, salesman=? where itemcode=? ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }

    // Bind parameters
    $stmt->bind_param('sssssssssssssssss',$_POST['contact'], $_POST['creditlimit'], $_POST['customer'], $_POST['middlen'], $_POST['phone'],
            $_POST['fax'], $_POST['company'], $_POST['altcontact'], $_POST['email'], $_POST['city'], $_POST['country'],
            $_POST['inactive'], $_POST['postcode'], $_POST['curr_cod'], $_POST['customerposting'], $_POST['salesman'],$_POST['itemcode']);
    }else{
    // Get the customer ID
    $ID = Triger_debtors($_POST['customer']);

    // Prepare the SQL statement using prepared statements to prevent SQL injection
    $sql = "INSERT INTO `debtors` 
        (itemcode, contact, creditlimit, customer, middlen, phone, fax, company, 
        altcontact, email, city, country, inactive, postcode, curr_cod, customerposting, salesman) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param('sssssssssssssssss',
            $ID, $_POST['contact'], $_POST['creditlimit'], $_POST['customer'], $_POST['middlen'], $_POST['phone'],
            $_POST['fax'], $_POST['company'], $_POST['altcontact'], $_POST['email'], $_POST['city'], $_POST['country'],
            $_POST['inactive'], $_POST['postcode'], $_POST['curr_cod'], $_POST['customerposting'], $_POST['salesman']);
    }
   // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Customer added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding customer: ' . $stmt->error]);
    }
}
// Close the statement and connection
$stmt->close();
$conn->close();
?>