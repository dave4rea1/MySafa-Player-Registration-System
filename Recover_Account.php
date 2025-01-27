<?php require_once("Include/Session.php") ?>
<?php require_once("Include/Functions.php") ?>
<?php require_once("Include/DB.php"); ?>

<?php
// recover account with email

if (isset($_POST["Submit"])) {

    $Email = mysqli_real_escape_string($Connection, $_POST["Email"]);

    if (empty($Email)) {
        $_SESSION["message"] = "Email Required";
        Redirect_to("Recover_Account.php");
    } elseif (!CheckEmailExistsOrNot($Email)) {
        $_SESSION["message"] = "Email Does Not Exist";
        Redirect_to("Recover_Account.php");
    } else {
        global $ConnectingDB;
        $Query = "SELECT * FROM admin WHERE email='$Email'";
        $Execute = mysqli_query($Connection, $Query);
        if ($admin = mysqli_fetch_array($Execute)) {
            $admin["FullName"];
            $admin["token"];
            $subject = "Password Reset";
            $body = "Hello " . $admin["FullName"] . ",\n\nYou have requested to reset your password.
            Please click on the link below to reset your password.\n\n
            http://localhost/visitapp/Reset_Password.php?token=" . $admin["token"];
            $SenderEmail = "From:kingdayve07@gmail.com";
            if (mail($Email, $subject, $body, $SenderEmail)) {
                $_SESSION["SuccessMessage"] = "Check your email to reset your password";
                Redirect_to("Login.php");
            } else {
                $_SESSION["message"] = "Something went wrong. Try again.";
                Redirect_to("Recover_Account.php");
            }
        }
    }
}
?>


 
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="Styles.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div>
    <?php echo Message(); ?>
    <?php echo SuccessMessage(); ?>
</div>


<div>
    <form action="Recover_Account.php" method="post">
        <div class="container">
            <div class="row">
                <div class="col-sm-3" id="centerpage">
                    <h1>Recover Account</h1>
                    <p>Fill up the form with correct values.</p>
                    <hr class="mb-3">

                    <label for="email"><b>Email Address</b></label>
                    <input class="form-control" id="email" type="email" name="Email" required>
                    <small class="text-muted">We'll never share your email with anyone else.</small>
                    <br>

                    <hr class="mb-3">
                    <!-- agree to terms and conditions -->
                    <!-- <input type="checkbox" name="checkbox" value="check" required> I agree to the
                    <a href="Terms_and_Conditions.php">Terms and Conditions</a><br> -->

                    <input class="btn btn-primary" type="submit" id="register" name="Submit" value="Submit">
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>