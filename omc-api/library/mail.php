<?php
class mail
{

	public $responce = array();
	private $_from,$_reply,$_host,$_username,$_password,$_port,$_smptsecure,$_type;
	private $_email,$_subject,$_message,$_plane_message,$_cc,$_bcc,$_attarchements;
	private $_from_name;
	private $_gmailusername,$_gmailclientid,$_gmailclientSecret,$_gmailrefreshtoken;

	public function __construct($email,$subject,$message,$plane_message, $image=null)
	{
		$this->db = new DB;
		$option = array();
		$option = $this->get_mail_settings();

		$this->_from = $option['mail_from'];
		$this->_host = $option['mail_host'];
		$this->_username = $option['mail_username'];
		$this->_password = $option['mail_password'];
		$this->_port = $option['mail_port'];
		$this->_from_name = $option['mail_from_name'];

		if(empty($this->_port)){
			$this->_port=25;
		}

		$this->_email = $email;
		$this->_subject = $subject;
		$template = new mail_template($message,$subject,$image);
		$this->_message = $template->get_default();
		$this->_plane_message = $plane_message;
	}

	public function get_mail_settings()
	{
		$mail_settings = array();
	 	$datas = $this->db->query("SELECT * FROM `mail_settings` LIMIT 1");
	 	if ($datas->results() && $datas->count > 0) {
			$data = $datas->first();
			$mail_settings['mail_from'] = $data->mail_from;
			$mail_settings['mail_from_name'] = $data->mail_from_name;
			$mail_settings['mail_host'] = $data->mail_host;
			$mail_settings['mail_username'] = $data->mail_username;
			$mail_settings['mail_password'] = $data->mail_password;
			$mail_settings['mail_port'] = $data->mail_port;
		 } else {
			trigger_error("no mail settings found in DB", E_USER_ERROR);
		}
		
		return $mail_settings;
	}

	public function addCC($mail='')
	{
		$this->$_cc = $mail;
	}


	public function addBCC($bcc='')
	{
		$this->$_bcc = $bcc;
	}


	public function addAttachment($attachments='')
	{
		$this->_attarchements = $attachments;
	}

	public function mailer()
	{
		// Create the Transport
		$transport = (new Swift_SmtpTransport($this->_host, $this->_port))
		->setUsername($this->_username)
		->setPassword($this->_password);

		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);
		// Create a message
		$message = new Swift_Message($this->_subject);
		$message->setFrom([$this->_from => $this->_from_name]);
		

		if ($this->_cc!==null & $this->_cc!=='') {
			if(is_array($this->_cc)){
				foreach ($this->_cc as $email) {
					$message->addCc($email);
				}
			}
			else{
				$message->addCc($this->_cc); // Add Blind Copy  recipient
			}
		}

		if ($this->_bcc!==null & $this->_bcc!=='') {
			if(is_array($this->_bcc)){
				foreach ($this->_bcc as $email) {
					$message->addBcc($email);// Add Blind Carbon Copy  recipient
				}
			}
			else{
				$message->addBcc($this->_bcc);// Add Blind Carbon Copy  recipient
			}
		}

		if(is_array($this->_email)){
			foreach ($this->_email as $email) {
				$message->addBcc($email);// Add Blind Carbon Copy  recipient
			}
		}
		else{
			$message->setTo($this->_email); // Add a recipient
		}

		$message->setBody($this->_message, 'text/html');
		$message->setReturnPath(EMAIL_RETURN);

		if ($this->_attarchements!==null & $this->_attarchements!=='') {
			if (is_array($this->_attarchements)) {
				foreach ($this->_attarchements as $value) {
					$message->attach(Swift_Attachment::fromPath($value))->setFilename(basename($value));
				}
			}else{
				$message->attach(Swift_Attachment::fromPath($this->_attarchements))->setFilename(basename($this->_attarchements));
			}
		}

		//Send the mail
		if(!$mailer->send($message)) {
		    $this->responce[0] = false;
		    $this->responce[1] = 'Mailer Error: Not sent';
		} else {
		    $this->responce[0] = true;
		    $this->responce[1] = 'Message has been sent';
		}
		return $this->responce;
	}

}