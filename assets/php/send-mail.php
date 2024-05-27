<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'phpmailer/autoload.php';

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
                 
    try {
        $mail->SMTPDebug = 2;                   // Enable verbose debug output
        $mail->isSMTP();                        // Set mailer to use SMTP
        $mail->Host       = 'mail.ablexperts.com';    // Specify main SMTP server
        $mail->SMTPAuth   = true;               // Enable SMTP authentication
        $mail->Username   = 'reachus@ablexperts.com';          // SMTP username
        $mail->Password   = '%BnCZ62WV5T-';         // SMTP password
        $mail->SMTPSecure = 'ssl';              // Enable TLS encryption, 'ssl' also accepted
        $mail->Port       = 465;                // TCP port to connect to set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        $mail->setFrom('reachus@ablexperts.com', 'ABLEXPERTS');           // Set sender of the mail
        $mail->addAddress('abletrainees@gmail.com');           // Add a recipient
        //$mail->addCC('', '');

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