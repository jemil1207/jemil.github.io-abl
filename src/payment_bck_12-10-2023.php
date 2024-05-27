<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
include '../config/constant.php';
require_once('sendMail.php');

use GuzzleHttp\Client;

$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

$redirectTo = SITE_URL.'/speakalive.html';


if ($request_method == "POST") {
    //var_dump($_POST);
    $errors = [];
    $inputs = [];
    if(isset($_POST['payment_submit']))
    {
        $validate = formValidation($_POST);
        $errors = $validate['errors'];
        $inputs = $validate['inputs'];
        if(count($errors) == 0)
        {
            makePhonepePayment($_POST);
        }
    }

    if(isset($_POST['code']))
    {
        $data = $_POST;
        $data['name'] = $_GET['name'];
        $data['email'] = $_GET['email'];
        phonePeCallback($data);
    }   
}else{

    header('Location: '.$redirectTo); 
    exit;
}


function makePhonepePayment($postData)
{
    try {

        $baseUrl    = PHONEPE_URL;
        $saltKey    = PHONEPE_SALT_KEY;
        $saltIndex  = PHONEPE_SALT_INDEX;
        $merchantId = PHONEPE_MERCHANT_ID;

        $amount = 1;
        $amountInPaise = $amount*100;

        $transactionId = 'MT'.strtotime('now');
        $callbackUrl = SITE_URL.'/src/payment.php?name='.$postData['name'].'&email='.$postData['email'];
        $userId = 'UID'.strtotime('now');
        $mobileNumber = $postData['phone'];

        $data['merchantId']                 = $merchantId;
        $data['merchantTransactionId']      = $transactionId;
        $data['merchantUserId']             = $userId;
        $data['amount']                     = (int) $amountInPaise;
        $data['redirectUrl']                = $callbackUrl;
        $data['redirectMode']               = 'POST';
        $data['callbackUrl']                = $callbackUrl;
        $data['mobileNumber']               = $mobileNumber;
        $data['paymentInstrument']['type']  = "PAY_PAGE";

        $encode = base64_encode(json_encode($data));
        $string = $encode.'/pg/v1/pay'.$saltKey;

        $sha256 = hash('sha256', $string);
        $finalXHeader =  $sha256.'###'.$saltIndex;

        $url = $baseUrl.'/pg/v1/pay';

        //error_log($encode); 
        //error_log($finalXHeader); 

        $client = new Client();

        $response = $client->request('POST', $url, [
                        'body' => '{"request": "'.$encode.'"}',
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'X-VERIFY' => $finalXHeader
                        ],
                    ]);

        $rData = json_decode($response->getBody());
    
        if($rData->code=='PAYMENT_INITIATED'){
            header("Location: ".$rData->data->instrumentResponse->redirectInfo->url);
            exit;
        }

    } catch (RequestException $e) {
        error_log($e); 
        header('Location: '.$redirectTo); 
        exit;
    }
}

function phonePeCallback($data)
{
    $baseUrl        = PHONEPE_URL;
    $saltKey        = PHONEPE_SALT_KEY;
    $saltIndex      = PHONEPE_SALT_INDEX;
    $merchantId     = $data['merchantId'];
    $transactionId  = $data['transactionId'];
    $userName       = $data['name'];
    $userEmail      = $data['email'];
    $paymentSuccessPage = SITE_URL.'/payment-success.html';
    $paymentErrorPage = SITE_URL.'/payment-fail.html';

    $finalXHeader = hash('sha256', '/pg/v1/status/'.$merchantId.'/'.$transactionId.$saltKey).'###'.$saltIndex;

    $url = $baseUrl.'/pg/v1/status/'.$merchantId.'/'.$transactionId;

    try {

        $client = new Client();

        $response = $client->request('GET', $url, [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'X-VERIFY' => $finalXHeader,
                            'X-MERCHANT-ID' => $merchantId
                        ],
                    ]);

        $rData = json_decode($response->getBody());


        if($rData->code=='PAYMENT_SUCCESS'){
            $amount = $rData->data->amount/100;

            $mailSubject =  SITE_NAME.': Successful workshop registration';
            $mailMessage = '<h2> HI '.$userName.', </h2>
                            <p>Your payment amount Rs.'.$amount.' received successfully.</p>
                            <p>Thanks for your interest with us.</p>
                            <p>Regards, </p>
                            <p>'.SITE_NAME.' team</p>';
            
            /** Send mail to Admin */
            $m = new SendMail();
            $result = $m->sendMessage(MAIL_FROM, ADMIN_EMAIL, $mailSubject, $mailMessage);
                           
            
            /** Send mail to User */
            $mailClass = new SendMail();
            $sendMail = $mailClass->sendMessage(MAIL_FROM, $userEmail, $mailSubject, $mailMessage);
            if($sendMail == true){
                header('Location: '.$paymentSuccessPage);
                exit;
            }
        }else{
            header('Location: '.$paymentErrorPage);
            exit;
        }

    } catch (RequestException $e) {
        error_log($e); 
        header('Location: '.$redirectTo); 
        exit;
    }

}

function formValidation($postData)
{
    $errors = [];
    $inputs = [];
    
    if(isset($postData['name']))
    {
        $name = sanitizeInput($postData['name']);
        if (preg_match('/^[A-Za-z\s\-\'\p{L}]+$/u', $name)) {
            $inputs['name'] = $name;
        } else {
            $errors['name'] = NAME_REQUIRED;
        }
    }else{
        $errors['name'] = NAME_REQUIRED;
    }
    

    if(isset($postData['email']))
    {
        $email = sanitizeInput($postData['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $inputs['email'] = $email;
        } else {
            $errors['email'] = EMAIL_INVALID;
        }
    }else{
        $errors['email'] = EMAIL_REQUIRED;
    }

    if(isset($postData['phone']))
    {
        $phone = sanitizeInput($postData['phone']);
        if(preg_match("/^[0-9]{10}$/", $phone)) {
            $inputs['phone'] = $phone;
        } else {
            $errors['phone'] = PHONE_INVALID;
        }
    }else{
        $errors['phone'] = PHONE_REQUIRED;
    }

    if(isset($postData['address']))
    {
        $address = sanitizeInput($postData['address']);
        if (!empty($address)) {
            $inputs['address'] = $address;
        } else {
            $errors['address'] = ADDRESS_REQUIRED;
        }
    }else{
        $errors['address'] = ADDRESS_REQUIRED;
    }

    $data['errors'] = $errors;
    $data['inputs'] = $inputs;

    return $data;
}

function sanitizeInput($input) {
    $data = trim($input);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}