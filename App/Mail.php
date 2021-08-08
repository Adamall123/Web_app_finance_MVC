<?php 

namespace App; 
use App\Config; 

class Mail
{
    public static function send($to, $subject, $text, $html)
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("adam.wojdylo.programista@gmail.com", "Adam Wojdylo");
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/plain", $text);
        $email->addContent(
            "text/html", $html
        );
        $sendgrid = new \SendGrid(Config::SENDGRID_API_KEY);
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
            }
}