<?php

require '../vendor/autoload.php';

namespace OOP_WP;

class Mail extends PHPMailer\PHPMailer{

	private static $user = 'mattura@gmail.com';
	private static $password = 'mattura!@#';
	private static $host = 'smtp.gmail.com'

	public function __construct()
	{
		$this->SMTPSecure = 'tls';
		$this->SMTPAuth = 'tls';
		$this->Port = 587;
		$this->Host = self::$host;
		$this->Password = self::$password;
	}

	public static function simple($to = [], $subject, $body, $from = null) {
		$from = empty($from) ? self::$user : $from;
		$mail = new self();
		$mail->setFrom($from);
		$this->isSMTP();
		$this->isHtml(true);

		if(empty($to)) {
			return false;
		}
		
		foreach ($to as $t) {
			$mail->addAddress($to);
		}

		$mail->addReplyTo($from);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = strip_tags(str_replace(‘<br />’,”\n”,$body));
		return $mail;
	}

	public static function send(&$mail) {
		if(!$mail->send()) {
		    return false;
		} else {
		    return true;
		}
	}
}