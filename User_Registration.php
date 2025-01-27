
<?php require_once("Include/Session.php")?>
<?php require_once("Include/Functions.php")?>
<?php require_once("Include/DB.php"); ?>


<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Include/PHPMailer/src/Exception.php';
require 'Include/PHPMailer/src/PHPMailer.php';
require 'Include/PHPMailer/src/SMTP.php';

if (isset($_POST["Submit"])) {
    // Initialize variables
    $Fullname = $_POST["Fullname"] ?? "";
    $Email = $_POST["Email"] ?? "";
    $Contact = $_POST["Contact"] ?? "";
    $Password = $_POST["Password"] ?? "";
    $ConfirmPassword = $_POST["ConfirmPassword"] ?? "";
    $Token = bin2hex(openssl_random_pseudo_bytes(40));

    // Validation checks
    if (empty($Fullname) || empty($Email) || empty($Contact) || empty($Password) || empty($ConfirmPassword)) {
        $_SESSION["message"] = "All fields must be filled out";
        Redirect_to("User_Registration.php");
    } elseif ($Password !== $ConfirmPassword) {
        $_SESSION["message"] = "Both passwords must match";
        Redirect_to("User_Registration.php");
    } elseif (strlen($Password) < 4) {
        $_SESSION["message"] = "Password should be greater than 3 characters";
        Redirect_to("User_Registration.php");
    } elseif (CheckEmailExistsOrNot($Email)) {
        $_SESSION["message"] = "Email already exists";
        Redirect_to("User_Registration.php");
    } else {
        // Insert user data into the database
        global $Connection;
        $Hashed_Password = Password_Encryption($Password);
        $Query = "INSERT INTO admin (FullName, Email, Contact, Password, token, active) 
                  VALUES ('$Fullname', '$Email', '$Contact', '$Hashed_Password', '$Token', 'OFF')";
        $Execute = mysqli_query($Connection, $Query);

        if ($Execute) {
            //send email to activate account
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->SMTPDebug = 2; 
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kingdayve07@gmail.com'; 
                $mail->Password = 'lvncwjqqhrsksdyk'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                // Sender info
                $mail->setFrom('kingdayve07@gmail.com', 'mySAFA');

                // Receiver info
                $mail->addAddress($Email, $Fullname);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Confirm Account';
                $mail->Body = "Hello $Fullname,<br><br>You have successfully registered on our platform. 
                               Please click on the link below to activate your account:<br><br>
                               <a href='http://localhost/mysafa/Activate.php?token=$Token'>Activation Link</a>";

                // Send email
                $mail->send();
                $_SESSION["SuccessMessage"] = "Check your email for activation link";
                Redirect_to("Login.php");
            } catch (Exception $e) {
                $_SESSION["message"] = "Mailer Error: " . $mail->ErrorInfo;
                Redirect_to("User_Registration.php");
            }
        } else {
            $_SESSION["message"] = "Something went wrong. Try again!";
            Redirect_to("User_Registration.php");
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title> 
    <link rel="stylesheet" href="Styles.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div>
        <?php echo Message(); ?>
        <?php echo SuccessMessage(); ?>
    </div>

    <div>
        <form action="User_Registration.php" method="post">
            <div class="container">
                <div class="row">
                    <div class="col-sm-3" id="centerpage">
                        <h1>Sign Up</h1>
                        <p>Fill up the form with correct values.</p>
                        <hr class="mb-3">
                        <label for="username"><b>Full Name</b></label>
                        <input class="form-control" id="fullname" type="text" name="Fullname" required>

                        <label for="email"><b>Email Address</b></label>
                        <input class="form-control" id="email" type="email" name="Email" required>

                        <label for="contact"><b>Contact</b></label>
                        <input class="form-control" id="contact" type="text" name="Contact" required>

                        <label for="password"><b>Password</b></label>
                        <input class="form-control" id="password" type="password" name="Password" required>

                        <label for="confirm_password"><b>Confirm Password</b></label>
                        <input class="form-control" id="confirm_password" type="password" name="ConfirmPassword" required>
                        <hr class="mb-3">
                        <input type="checkbox" name="checkbox" value="check" required> I agree to the 
                        <a href="Terms_and_Conditions.php">Terms and Conditions</a><br>
                        <p>Already have an account? <a href="Login.php">Login</a></p>

                        <input class="btn btn-primary" type="submit" id="register" name="Submit" value="Sign Up">
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
