<!DOCTYPE html>

<html>
    <head>
        <title>Simple</title> 
    </head>
    <body>
        <h1>Simple page</h1>
        <p>
            <?php
                // echo "Hello World!";

                $password="Milan";
                $BlowFish_Hash_Format="$2y$12$";
                $salt="MYNAMEisMilanandIamfromItaly";
                echo "Length: ".strlen($salt);
                $Formatting_Blowfish_With_Salt=$BlowFish_Hash_Format.$salt;
                $hash=crypt($password,$Formatting_Blowfish_With_Salt);
                echo "<br>";
                echo $hash;

                $password="Lagos";
                $BlowFish_Hash_Format="$2y$10$";
                $salt="MYNAMEisMilanandIamfromNigeria";
                echo "Length: ".strlen($salt);
                $Formatting_Blowfish_With_Salt=$BlowFish_Hash_Format.$salt;
                $hash=crypt($password,$Formatting_Blowfish_With_Salt);
                echo "<br>";
                echo $hash;
            ?>
        </p>
</html>