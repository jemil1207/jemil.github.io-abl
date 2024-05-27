<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'assets/php/phpmailer/autoload.php';

// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name   = $_POST['name'];
    $emailFrom  = $_POST['email'];
    $phone  = $_POST['phone'];
    $address = $_POST['address'];

    $subject = 'A New Application form received from ' .$name;

    $htmlContent = '<h2> Application Form Received </h2>
                    <p><b>Applicant Name:</b>'. $name .'</p>
                    <p> <b>Email: </b> '.$emailFrom .'</p>
                    <p> <b>Phone Number: </b> '.$phone .'</p>
                    <p> <b>Address: </b> '.$address.'</p>';
                 

    $username = 'abletrainees@gmail.com'; 
    $password = 'vekdeihzmxlpaltt'; 

    try {
        $mail->SMTPDebug = 2;                   // Enable verbose debug output
        $mail->isSMTP();                        // Set mailer to use SMTP
        $mail->Host       = 'smtp.gmail.com;';    // Specify main SMTP server
        $mail->SMTPAuth   = true;               // Enable SMTP authentication
        $mail->Username   = $username;          // SMTP username
        $mail->Password   = $password;         // SMTP password
        $mail->SMTPSecure = 'tls';              // Enable TLS encryption, 'ssl' also accepted
        $mail->Port       = 587;                // TCP port to connect to set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        $mail->setFrom($emailFrom, $name);           // Set sender of the mail
        $mail->addAddress('abletrainees@gmail.com');           // Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        $mail->send();
        echo 'Application Submitted Successfully';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}