<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_email($subject, $body, $receiver_mail, $receiver_title){
    require LIBS_DIR.'vendor/autoload.php';
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = EMAIL__HOST;                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = EMAIL__USERNAME;                     //SMTP username
    $mail->Password   = EMAIL__PASSWORD;                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
    $mail->Port       = EMAIL__PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_SMTPS`

    //Recipients
    $mail->setFrom(EMAIL__SENDER_ADDR, EMAIL__SENDER);
    $mail->addAddress($receiver_mail, $receiver_title);     //Add a recipient
    
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = "";

    $mail->send();
}
?>