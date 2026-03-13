<?php


    // PHPMailer instance
    $mail = new PHPMailer(true);
    $config = include('../config.php'); // Import SMTP settings
    
    $sendcc  = $_POST['sendcc'];
    $sendto  = $_POST['sendto'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];
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
        
        $mail->setFrom($config['from_email'],$config['from_name']); // Sender's email and name
        $mail->addAddress($sendto);    // Add recipient
        $mail->addCC($sendcc);
        // Email content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $content;
        $mail->AltBody = $content; // Fallback for plain text
       // Send email
        $mail->send();
        echo json_encode( [
            'success' => true,
            'error' => null
        ]);
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}"); // Log the error
         echo json_encode( [
            'success' => false,
            'error' => $mail->ErrorInfo
        ]);
    }

