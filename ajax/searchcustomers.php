<?php
require '../db_connection.php'; 

// Set the character set to UTF-8
$conn->set_charset("utf8mb4");

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']); // Escape user input to prevent SQL injection

    // Prepare the SQL statement
    $stmt = $conn->prepare("
        SELECT 
            itemcode AS code,
            customer AS name,
            curr_cod AS currency,
            IFNULL(salesman, '') AS salespersoncode
        FROM debtors 
        WHERE customer LIKE CONCAT('%', ?, '%') 
        LIMIT 10
    ");
    
    if ($stmt) {
        // Bind the parameter and execute the query
        $stmt->bind_param("s", $query);
        $stmt->execute();

        // Fetch the results
        $result = $stmt->get_result();
        $customers = $result->fetch_all(MYSQLI_ASSOC);

        // Return the results as JSON
        header('Content-Type: application/json');
        echo json_encode($customers);

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['message' => 'Failed to prepare the statement: ' . $conn->error]);
    }
} else {
    echo json_encode(['message' => 'Query parameter is required']);
}

// Close the connection
$conn->close();

?>
