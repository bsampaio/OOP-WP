<?php

namespace OOP_WP;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

class Mail extends \PHPMailer 
{
    private static $from = MAIL_FROM_DEFAULT;
	private static $user = MAIL_USERNAME;
	private static $password = MAIL_PASSWORD;
	private static $host = MAIL_SMTP_SERVER;
    private static $debug = MAIL_DEBUG;

	public function __construct()
	{
        $this->CharSet = 'utf-8';
		if(MAIL_SSL) {
			$this->SMTPSecure = 'tls';
			$this->SMTPAuth = true;
            $this->SMTPDebug = self::$debug;
		}
        $this->Username = self::$user;
		$this->Port = MAIL_PORT;
		$this->Host = self::$host;
		$this->Password = self::$password;
	}

	public static function simple($to = [], $subject, $body, $from = null) {
		$from = empty($from) ? self::$from : $from;
		$mail = new self();
		$mail->setFrom($from);
		if(MAIL_SSL) {
           $mail->isSMTP();
		}
		$mail->isHtml(true);

		if(empty($to)) {
			return false;
		}
		
		foreach ($to as $t) {
			$mail->addAddress($t);
		}

		$mail->addReplyTo($from);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = strip_tags(str_replace("<br />","\n",$body));
		return $mail;
	}
        
    public function getError() {
        return $this->ErrorInfo;
    }

	public static function deliver(&$mail) {
		if(!$mail->send()) {
            echo $mail->getError();
		    return false;
		} else {
		    return true;
		}
	}
}