<?php 
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

// Get confirmation code from the URL
$confirmation_code = isset($_GET['code']) ? $_GET['code'] : '';

if ($confirmation_code) {
    // Update the registration status to 'Confirmed'
    $sql = "UPDATE registration SET Status = 'Confirmed' WHERE ConfirmationCode = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $confirmation_code);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Registration confirmed successfully!";
    } else {
        $_SESSION['error_message'] = "Invalid confirmation code or registration already confirmed.";
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['error_message'] = "No confirmation code provided.";
}

Redirect_to("Thank_You.php");
?>
