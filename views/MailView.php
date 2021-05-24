<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/User.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


class MailView extends View
{
    public static function send($subject, $body, $recipient, $redirect_to)
    {
        global $email_conf;
        global $app_path;
        global $send_emails;

        if(!isset($recipient->external_user))
            $recipient->external_user = $recipient->get_external_user();
        if($recipient->external_user && $send_emails)
        {
            if($recipient->messages_allow)
            {
                try 
                {
                    //Server settings
                    $mail = new PHPMailer(true);
                    $mail->SMTPDebug = SMTP::DEBUG_SERVER;             
                    $mail->isSMTP();                             
                    $mail->Host       = $email_conf['host'];                    
                    $mail->SMTPAuth   = true;                          
                    $mail->Username   = $email_conf['name'];                  
                    $mail->Password   = $email_conf['password'];               
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
                    $mail->Port       = $email_conf['port'];                                  
                    //Recipients
                    $mail->setFrom($email_conf['name'], 'Dzial IT');
                    $mail->addAddress($recipient->external_user->email);
                    //Content
                    $mail->isHTML(true);                           
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AltBody = 'Wiadomosc wysylana automatycznie. Prosimy na nia nie odpowiadac';
                    $mail->send();

                    echo("<script>location.href='$app_path$redirect_to';</script>");
                    exit();
                } 
                catch (Exception $e) 
                {
                    $this->add_message('error', "Wystąpił błąd - {$mail->ErrorInfo}");
                }
            }
        }

        echo("<script>location.href='$app_path$redirect_to';</script>");
        exit();
    }
}

?>