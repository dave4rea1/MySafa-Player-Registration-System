<?php 

// mail('kingdayve07@gmail.com', 'Testing', 
// 'This is a test email', 'From: kingdayve07@gmail.com');

$emailTo="kingdayve07@gmail.com";
$subject="Testing";
$body="This is a test email";
$headers="From:kingdayve07@gmail.com";
if(mail($emailTo, $subject, $body, $headers)){
    echo "Mail sent successfully!";
}
else{
    echo "Mail not sent!";
}



?>