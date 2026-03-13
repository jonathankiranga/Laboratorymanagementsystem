<?php
// Database connection
require '../db_connection.php'; 
require '../vendor/autoload.php'; // Include PHPMailer via Composer
require '../functions/functions.php'; // Include PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



// Helper function to validate the reset token
function isTokenValid($conn, $token) {
    $sql = "SELECT user_id, expires_at FROM Password_Reset WHERE reset_token = ? AND is_used = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $stmt->close();
        return false; // Token not found or already used
    }
    
    // Fetch expiration time
    $stmt->bind_result($user_id, $expires_at);
    $stmt->fetch();
    $stmt->close();

    // Check if the token is expired
    if (new DateTime() > new DateTime($expires_at)) {
        return false; // Token expired
    }

    return $user_id; // Return user ID if valid
}

$response = ['success' => false, 'message' => ''];

// Register a new user (Creating the user and keys)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $username  = $_POST['username'] ?? '';
    $email     = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role      = $_POST['role'] ?? 'user'; // Default role is 'user'
    $password  = $_POST['password'] ?? ''; // Default password if empty
    $telephone = $_POST['telephone'] ?? ''; // Default phone number

    // Validate inputs
    if (empty($username) || empty($password) || empty($email)) {
        $response['message'] = 'Username, password, and email are required.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
        echo json_encode($response);
        exit;
    }

    try {
        // Start a transaction
        $conn->autocommit(false);
     // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $response['message'] = 'Username or email already exists. Try password recovery.';
            $stmt->close();
            echo json_encode($response);
            exit;
        }
        $stmt->close();

        // Generate public and private keys
        $keyPair = openssl_pkey_new();
        openssl_pkey_export($keyPair, $privateKey);
        $publicKey = openssl_pkey_get_details($keyPair)['key'];
        $password_hash = hashPassword($password);
     
       
        // Insert user into the database
        $sql = "INSERT INTO Users (username, email, full_name, role, password_hash,  created_at, telephone, status) 
                VALUES (?, ?, ?, ?,  ?, NOW(), ?, 'active')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
              $response['message'] ="Error preparing statement: " . $conn->error;
              echo json_encode($response);
              exit;
        }
        $stmt->bind_param("ssssss", $username, $email, $full_name, $role, $password_hash, $telephone);
        $stmt->execute();
         if ($stmt->affected_rows <= 0) {
              $response['message'] ="Failed to insert user: " . $stmt->error;
              $stmt->close();
              echo json_encode($response);
              exit;
        }
         // Get the newly created user ID
        $user_id = $conn->insert_id;
        $dir = "userkeys/$user_id";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents("$dir/private_key.pem", $privateKey) === false) {
               $response['message'] ="Failed to write private key.";
               echo json_encode($response);
               exit;
        } else {
            chmod("$dir/private_key.pem", 0600);
        }

        if (file_put_contents("$dir/public_key.pem", $publicKey) === false) {
              $response['message'] ="Failed to write public key.";
              echo json_encode($response);
              exit;
        } else {
            chmod("$dir/public_key.pem", 0644);
        }

     
        $stmt->close();

        
        $conn->commit();

        $response = ['success' => true, 'message' => 'User created successfully. Wait for the Admin to assign your role.'];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response = ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
    } finally {
        $conn->autocommit(true);
     }
     
  $conn->close();
}


// Handle password reset request (send a reset token to email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'reset_request') {
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Invalid email address.'];
        echo json_encode($response);
        exit;
    }
    
    
   // Check if the email exists in the Users table
    $sql = "SELECT user_id FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Fetch user ID
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Generate reset token
        $domain = $_SERVER['HTTP_HOST'];
        $reset_token = generateResetToken();
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Insert reset token into the Password_Reset table
        $stmt->close(); // Close previous query
        $sql = "INSERT INTO Password_Reset (user_id, reset_token, created_at, expires_at, is_used) VALUES (?, ?, NOW(), ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $reset_token, $expires_at);
        $stmt->execute();
        $stmt->close();
        // Send reset link to the user's email
        $reset_link = "https://$domain/reset_password.php?token=$reset_token";
        
        $result =sendPasswordResetEmail($email, $domain, $reset_token,$reset_link );
         if ($result['success']) {
            $response = ['success' => true, 'message' => 'Password reset link has been sent to your email!'];
        } else {
            $response = ['success' => false, 'message' => $result['error']];
        }
        
        
    } else {
        $response = ['success' => false, 'message' => 'No user found with that email address!'];
    }

   
    $conn->close();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  $_POST['action'] === 'validate_token') {
    // Reset the password
    $reset_token = $_POST['reset_token'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    $user_id = isTokenValid($conn, $reset_token);
    if (!$user_id) {
        $response = ['success' => false, 'message' => 'Invalid or expired token.'];
        echo json_encode($response);
        exit;
    }

    try {
        // Begin transaction
        $conn->autocommit(false);
    // Update the user's password
        $password_hash = hashPassword($new_password);
        $sql = "UPDATE Users SET password_hash = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
              $stmt->close();
              $response['message'] ="Failed to update password.";
              echo json_encode($response);
              exit;
        }

        $stmt->close();
        // Generate new public and private keys
        $keyPair = openssl_pkey_new();
        openssl_pkey_export($keyPair, $privateKey);
        $publicKey = openssl_pkey_get_details($keyPair)['key'];
      
        
        $dir = "userkeys/$user_id";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents("$dir/private_key.pem", $privateKey) === false) {
               $response['message'] ="Failed to write private key.";
               echo json_encode($response);
               exit;
        } else {
            chmod("$dir/private_key.pem", 0600);
        }

        if (file_put_contents("$dir/public_key.pem", $publicKey) === false) {
              $response['message'] ="Failed to write public key.";
              echo json_encode($response);
              exit;
        } else {
            chmod("$dir/public_key.pem", 0644);
        }
       
        // Mark the token as used
        $sql = "UPDATE Password_Reset SET is_used = 1 WHERE reset_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $reset_token);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
             $stmt->close();
              $response['message'] ="Failed to mark token as used.";
              echo json_encode($response);
              exit;
        }

        // Commit transaction
        $conn->commit();
        $response = ['success' => true, 'message' => 'Password reset successfully.'];
    } catch (Exception $e) {
        // Roll back on error
        $conn->rollback();
        $response = ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
    } finally {
        $conn->autocommit(true);
        $conn->close();
    }
         
} 
  

function sendPasswordResetEmail($email, $domain, $reset_token,$reset_link ) {
    // PHPMailer instance
    $mail = new PHPMailer(true);
    $config = include('../config.php'); // Import SMTP settings

    try {
        // Server settings
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = $config['smtp_auth'];
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'] === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $config['smtp_port'];
       // TCP port (587 for TLS, 465 for SSL)
       // Recipient
        $mail->setFrom($config['from_email'], $config['from_name']); // Sender's email and name
        $mail->addAddress($email);                            // Add recipient

        // Email content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click this link to reset your password: <a href='$reset_link'>$reset_link</a>";
        $mail->AltBody = "Click this link to reset your password: $reset_link"; // Fallback for plain text

        // Send email
        $mail->send();
        return [
            'success' => true,
            'error' => null
        ];
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}"); // Log the error
         return [
            'success' => false,
            'error' => $mail->ErrorInfo
        ];
    }
}

// Return response as JSON
echo json_encode($response);
?>
