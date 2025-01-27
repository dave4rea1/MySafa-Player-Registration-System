
<?php require_once("Include/Session.php")?>
<?php require_once("Include/Functions.php")?>
<?php require_once("Include/DB.php"); ?>
<?php
if(isset($_GET['token'])){
    $TokenFromURL=$_GET['token'];

if (isset($_POST["Submit"])) {
    // Initialize variables
   
    $Password = "";
    $ConfirmPassword = "";
    

    // set POST variables and assign them
  
    if (isset($_POST["Password"])) {
        $Password = mysqli_real_escape_string($Connection, $_POST["Password"]);
    }
    if (isset($_POST["ConfirmPassword"])) {
        $ConfirmPassword = mysqli_real_escape_string($Connection, $_POST["ConfirmPassword"]);
    }

    // Validation checks
    if (empty($Password) || empty($ConfirmPassword)) {
        $_SESSION["message"] = "All fields must be filled out";
    } elseif ($Password !== $ConfirmPassword) {
        $_SESSION["message"] = "Both passwords must match";
    } 
     elseif (strlen($Password) < 4) {
        $_SESSION["message"] = "Password should be greater than 3 characters";
      
   
    }  



    else {
        // Insert user data into the database
        global $ConnectingDB;
        $Hashed_Password = Password_Encryption($Password);
        $Query = "UPDATE admin SET password='$Hashed_Password',token='' WHERE token='$TokenFromURL'";
        $Execute = mysqli_query($Connection, $Query);
        if($Execute){
            $_SESSION["SuccessMessage"]="Password Changed Successfully";
            Redirect_to("Login.php");
        }
        else{
            $_SESSION["message"]="Something went wrong. Try again.";
            Redirect_to("Reset_Password.php?token=$TokenFromURL");
        }
      
    }
}


    


}
?>
<?php ?>

<!DOCTYPE html>

<html>
    <head>
        <title>Create New Password</title> 
        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="Styles.css">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div>
        <?php echo Message(); ?>
        <?php echo SuccessMessage(); ?>
        </div>

        <!-- signup page in bootstrap -->
        <div>
        <form action="Reset_Password.php?token=<?php echo $TokenFromURL; ?>" 
        method="post" >
            <div class="container" >
                <div class="row" >
                    <div class="col-sm-3" id="centerpage" >
                        <h1>Reset Password</h1>
                        <p>Fill up the form with correct values.</p>
                        <hr class="mb-3">

                        <label for="password"><b>New Password</b></label>
                        <input class="form-control" id="password" type="password" name="Password" required>

                        <label for="confirm_password"><b>Confirm Password</b></label>
                        <input class="form-control" id="confirm_password" type="password" name="ConfirmPassword" required>
                        <hr class="mb-3">
                       
                      
    
                        <input class="btn btn-primary" type="submit" id="register" name="Submit" value="Submit">
                    </div>
                </div>
            </div>
        </form>

        </div>


</body>

</html>