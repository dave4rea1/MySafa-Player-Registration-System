<?php require_once("Include/Session.php") ?>
<?php require_once("Include/Functions.php") ?>
<?php
    $_SESSION["User_Id"]=null;
    $ExpireTime=time()-86400;
    setcookie("email",null,$ExpireTime);
    setcookie("password",null,$ExpireTime);
    setcookie("username",null,$ExpireTime);
    session_destroy();
    Redirect_to("Login.php");

?>