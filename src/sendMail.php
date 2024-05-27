<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendMail extends PHPMailer
{
    private $_host = MAIL_HOST;
    private $_user = MAIL_USERNAME;
    private $_password = MAIL_PASSWORD;

    public function __construct($exceptions=true)
    {
        $this->Host = $this->_host;
        $this->Username = $this->_user;
        $this->Password = $this->_password;
        $this->Port = 465;
        $this->SMTPAuth = true;
        $this->SMTPSecure = 'ssl';
        $this->isSMTP();
        $this->isHTML(true);  
        parent::__construct($exceptions);
    }

    public function sendMessage($from, $to, $subject, $body, $attachment=null)
    {
        $this->setFrom($from);
        $this->addAddress($to);
        $this->Subject = $subject;
        $this->Body = $body;
        if($attachment!=null)
        {
            $this->addAttachment("../assets/uploads/".$attachment);
        }
      
        return $this->send();
    }
}
?>