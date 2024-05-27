<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
include '../config/constant.php';
require_once('sendMail.php');

use GuzzleHttp\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

$invoiceId = 'INV-001';
$userName = 'Sharmila';
$userEmail = 'sharmilanedumaran@gmail.com';
$phone = '8973487098';
$userAddress = '31, Big street';
$registerFor = 'Speak AAlive';
$amount = '100';

$template = file_get_contents('invoice.html');

$path = dirname(__FILE__, 2).'/assets/images/logo.png';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$logo = 'data:image/' . $type . ';base64,' . base64_encode($data);

$variables = array();
$variables['siteLogo']      = $logo;
$variables['transactionId'] = 'TESTTRANS';
$variables['invoiceNo']     = $invoiceId;
$variables['createdAt']     = date('d-m-Y');
$variables['name']          = $userName;
$variables['email']         = $userEmail;
$variables['phone']         = $phone;
$variables['address']       = $userAddress;
$variables['paymentMethod'] = 'UPI';
$variables['registeredFor'] = $registerFor;
$variables['amount']        = $amount;

foreach($variables as $key => $value)
{
    $template = str_replace('{{ '.$key.' }}', $value, $template);
}

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->load_html($template);
$dompdf->render();
$output = $dompdf->output();
$folder = '../assets/uploads';
$filename = 'invoice_'.time().'.pdf';
file_put_contents($folder.'/'.$filename, $output);

$mailSubject =  SITE_NAME.': Successful workshop registration';

/** Send mail to Admin */
$adminMailMessage = '<h2> Hi Admin, </h2>
                    <p>'.$userName.' has paid amount Rs.'.$amount.' for '.$registerFor.'</p>
                    <p>Regards, </p>
                    <p>'.SITE_NAME.' team</p>';
                    
$m = new SendMail();
$result = $m->sendMessage(MAIL_FROM, ADMIN_EMAIL, $mailSubject, $adminMailMessage, $filename);
                

/** Send mail to User */
$userMailMessage = '<h2> HI '.$userName.', </h2>
                    <p>Your payment amount Rs.'.$amount.' received successfully.</p>
                    <p>Thanks for your interest with us.</p>
                    <p>Regards, </p>
                    <p>'.SITE_NAME.' team</p>';

$mailClass = new SendMail();
$sendMail = $mailClass->sendMessage(MAIL_FROM, $userEmail, $mailSubject, $userMailMessage, $filename);
?>