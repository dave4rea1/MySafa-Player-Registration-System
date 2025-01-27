<?php require_once("Include/Session.php")?>
<?php require_once("Include/Functions.php")?>
<?php require_once("Include/DB.php"); ?>
<?php

global $ConnectingDB;
if(isset($_GET['token'])){
    $TokenFromURL=$_GET['token'];
    $Query="UPDATE admin SET active='ON' WHERE token='$TokenFromURL'";
    $Execute=mysqli_query($Connection,$Query);
    if($Execute){
        $_SESSION["SuccessMessage"]="Account Activated Successfully";
        Redirect_to("Login.php");
    }
    else{
        $_SESSION["message"]="Something went wrong";
        Redirect_to("User_Registration.php");
    }
}

?>