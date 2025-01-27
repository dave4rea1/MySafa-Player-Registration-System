<?php
    // require_once("Include/DB.php");
    require_once("db_connection.php");
    

    // function Redirect_to($New_Location) {
    //     // Check for any output
    //     if (ob_get_contents()) {
    //         ob_clean(); // Clean output buffer
    //     }

    //     // Perform the redirection
    //     header("Location: " . $New_Location);
    //     exit; // Terminate script execution
    // }
    function Redirect_to($New_location) {
        header("Location:" . $New_location);
        exit;
    }


    function Password_Encryption($password){
        $BlowFish_Hash_Format="$2y$10$";
        $Salt_Length=22;
        $salt=Generate_Salt($Salt_Length);
        $Formatting_Blowfish_With_Salt=$BlowFish_Hash_Format.$salt;
        $hash=crypt($password,$Formatting_Blowfish_With_Salt);
        return $hash;
    }

    function Generate_Salt($length){
        $Unique_Random_String=md5(uniqid(mt_rand(),true));
        $Base64_String=base64_encode($Unique_Random_String);
        $Modified_Base64_String=str_replace('+','.',$Base64_String);
        $salt=substr($Modified_Base64_String,0,$length);
        return $salt;
    }

    function Password_Check($password, $Existing_Hash){
        $hash=crypt($password,$Existing_Hash);
        if($hash === $Existing_Hash){
            return true;
        }else{
            return false;
        }
    }

 
    function CheckEmailExistsOrNot($Email){
        global $conn;
        $Query="SELECT * FROM admin WHERE email='$Email'";
        $Execute=mysqli_query($conn,$Query);
        if(mysqli_num_rows($Execute)>0){
            return true;
        }else{
            return false;
        }

    }


    function Login_Attempt($Email, $Password) {
        global $conn; // Use your actual database connection variable
    
        $sql = "SELECT * FROM admin WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $Email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $found_account = $result->fetch_assoc();
            if (password_verify($Password, $found_account['Password'])) {
                return $found_account;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function ConfirmingAccountActiveStatus(){
        global $conn;
        $Query="SELECT * FROM admin WHERE active='ON'";
        $Execute=mysqli_query($conn,$Query);
        if(mysqli_num_rows($Execute)>0){
            return true;
        }else{
            return false;
        }
    }
    
    function login(){
        if(isset($_SESSION["AdminID"]) || isset($_COOKIE["Email"])){
            return true;
        }
    }

    function Confirm_Login(){
        if(!login()){
            $_SESSION["message"]="Login Required";
            Redirect_to("Login.php");
        }
        
    }

    
    
?> 