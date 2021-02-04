<?php
class sysmail{

	public static function  quicksend($email_to,$msg,$sub=null){
		if ($sub===null) {
			$sub='Message from '.APP_NAME.' admin';
		}
		$msg = wordwrap($msg, 70, "\r\n");// wordrap if text is larger than 70 words
		return self::sendmail($email_to,$sub,$msg);
	}	

	/**default email sending templates can be used for sending all type of mails
	 * @param $email recipiant email address
	 * @param $msg message to send to user
	 * @param $subject email subject
	 * @return boolean
	 */
	public static function mail_default($email, $msg,$subject, $attarchements = null)
	{
		$template = new mail_template($msg, $subject);
		$message = $template->get_default();
		$message = wordwrap($message, 70, "\r\n");
		return self::sendmail($email,$subject,$message, $attarchements = null);
	}

	/**sending welcome message and account activation link
	 * @param $email recipiant email
	 * @param $msg message to send to user
	 * @param $activationcode activation link containing the code for activation
	 * @return boolean
	 */
	public static function new_account($email, $msg,$activationcode)
	{
		$template = new mail_template($msg, "New Account Activation");
		$message = $template->get_newaccount($activationcode);
		$message = wordwrap($message, 70, "\r\n");
		return self::sendmail($email,"New Account Activation",$message);
    }
    
	/**sending email verification message and account activation link
	 * @param $email recipiant email
	 * @param $msg message to send to user
	 * @param $activationcode activation link containing the code for activation
	 * @return boolean
	 */
	public static function verify_email($email, $msg,$activationcode)
	{
		$template = new mail_template($msg, "Account Verification");
		$message = $template->email_verification($activationcode);
		$message = wordwrap($message, 70, "\r\n");
		return self::sendmail($email,"Account Verification",$message);
	}

	/**sending password reset instruction
	 * @param $email recipiant email
	 * @param $activationcode activation link containing the code for activation
	 * @return boolean
	 */
	public static function reset_password($email, $activationcode)
	{
		$template = new mail_template("", "Password Reset Instruction");
		$message = $template->get_resetpassword($activationcode);
		$message = wordwrap($message, 70, "\r\n");
		return self::sendmail($email,"Password Reset Instruction",$message);
	}

	/**this function gets called by the various types of mail after getting its templates
	 * @param $emailto recipiant email
	 * @param $message message to send to user
	 * @param $subject email subject
	 * @param $attarchment should contain theattacment tosend
	 * @return boolean
	 */
	public static function sendmail($mailto, $subject, $html, $attarchements = null){
		// Create the Transport
		$transport = (new Swift_SmtpTransport(MAIL_HOST, 25))
			->setUsername(MAIL_USER)
			->setPassword(MAIL_PASSWORD);

		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);
		// Create a message
        $message = new Swift_Message($subject);
        $message->setFrom([MAIL_USER => $subject]);
        $message->setTo($mailto);
		$message->setBody($html,'text/html');
		$message->setReturnPath(EMAIL_RETURN);
			
		if ($attarchements!==null & $attarchements!=='') {
			if (is_array($attarchements)) {
				foreach ($attarchements as $value) {
					$message->attach(Swift_Attachment::fromPath($value))->setFilename(basename($value));
				}
			}else{
				$message->attach(Swift_Attachment::fromPath($attarchements))->setFilename(basename($attarchements));
			}
		}

		//Send the mail
		if(!$mailer->send($message)) {
            return false;
		} else {
            return true;
		}
	}
}