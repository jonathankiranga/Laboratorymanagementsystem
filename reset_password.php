<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In...</title>
    <link rel="stylesheet" href="css/default.css" type="text/css"/>
</head>
<body>
<div>
        <div id="errorDisplay" class="error-message"></div> <!-- Placeholder for error messages -->
        <h3>Reset</h3>
        <input type="hidden" id="token"  placeholder="Enter password">
        <input type="password" id="newpassword" class="login-input" placeholder="Enter password">
        <input type="password" id="confirmpassword" class="login-input" placeholder="Confirm password">
        <button class="close-modal"  onclick="resetpassword()">Reset Password</button>
</div>

<script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<script>
$(document).ready(function () {
    // Parse query string parameters
    const queryParams = new URLSearchParams(window.location.search);
  if (queryParams.has('token')) {
        // Get the value of the 'token' parameter
        const tokenValue = queryParams.get('token');
        // Set the value of the input with id 'token'
        $('#token').val(tokenValue);
    }
});
</script>

<script>
    const typingSpeed = 50;
    const lineDelay = 1000;
    let currentLine = 0;

   
 // General-purpose typeLine function for custom messages
    function generalPurposeTypeLine(message, targetElementId, callback = null, index = 0) {
        const targetElement = document.getElementById(targetElementId);
         // Clear the target element on the first call
        if (index === 0) {
            targetElement.innerHTML = ''; // Clear the content
        }
        
        if (index < message.length) {
            targetElement.innerHTML += message.charAt(index);
            setTimeout(() => generalPurposeTypeLine(message, targetElementId, callback, index + 1), typingSpeed);
        } else if (callback) {
            callback();
        }
    }


    function resetpassword(){
            const token = document.getElementById("token").value;
            const confirmpassword = document.getElementById("confirmpassword").value;
            const password = document.getElementById("newpassword").value;
            
           console.log('confirmpassword:',confirmpassword);
           console.log('password:',password);
      
            $.ajax({
                url: "ajax/register_user.php", // The URL to your PHP file
                type: "POST", // HTTP method
                data: {
                    action: "validate_token",
                    reset_token: token,
                    confirm_password: confirmpassword,
                    new_password: password
                },
                success: function(response) {
                    // Assuming data is a JSON object
                     const data = JSON.parse(response);
                    if (data.success) {
                        toastr.success(data.message); // Display success message
                        window.location.href = "index.php";  // Update to the correct homepage URL
                  } else {
                        generalPurposeTypeLine(data.message, "errorDisplay"); // Display error message
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error("Error: " + error); // Handle any error in the AJAX request
                }
            });

    }
    
      // Display error message using typeLine
    function displayErrorMessage(message) {
        document.getElementById("errorDisplay").innerHTML = ""; // Clear any previous error
        generalPurposeTypeLine(message, "errorDisplay"); // Type out the error message
    }

   

</script>
</body>
</html>
