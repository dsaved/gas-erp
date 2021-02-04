<?php
class mail_template{

	public function __construct($message, $header = "",$image=null)
	{
        $this->file = new Files();
		$this->datas = array();
		$this->datas['date'] = date('Y');
		$this->datas['header'] = $header;
		$this->datas['image'] = $this->getImage($image);
		$this->datas['message'] = $message;
		$this->datas['app_name'] = APP_NAME;
		$this->datas['poweredby'] = POWERED_BY;
		$this->datas['sitelink'] = SITE_LINK;
		$this->datas['address'] = APP_ADDRESS;
		$this->datas['disclaimer'] = str_replace('{{app_name}}', APP_NAME, DISCLAIMER);
	}

	public function getImage($image){
		return !empty($image)?'<img src="'.$image.'" >':'';
	}

	/**gets the template for use when sending emails. in this case default is used
	 * @param null
	 * @return string
	 */
	public function get_default()
	{
		$template = Files::getInstance()->read(APP_BASE_URL."templates/default.html");
		foreach($this->datas as $key => $value)
		{
			$template = str_replace('{{'.$key.'}}', $value, $template);
		}
		return $template;
	}

	/**gets the new account email template for use when sending emails
	 * @param $code activation code which is a link that will be clicked by the user 
	 * in order to activate his/her account
	 * @return string
	 */
	public function get_newaccount($code)
	{
		$this->datas['activationcode'] = $code;
		$template = $this->file->read(APP_BASE_URL."templates/new-account.html");
		foreach($this->datas as $key => $value)
		{
			$template = str_replace('{{'.$key.'}}', $value, $template);
		}
		return $template;
	}

	/**gets the account verification email template for use when sending emails
	 * @param $code activation code which is a link that will be clicked by the user 
	 * in order to activate his/her account
	 * @return string
	 */
	public function email_verification($code)
	{
		$this->datas['activationcode'] = $code;
		$template = $this->file->read(APP_BASE_URL."templates/email-verification.html");
		foreach($this->datas as $key => $value)
		{
			$template = str_replace('{{'.$key.'}}', $value, $template);
		}
		return $template;
	}

	/**gets thereset password email template for use when sending emails
	 * @param $code account reset code which is a link that will be clicked by the user 
	 * in order to reset his/her account
	 * @return string
	 */
	public function get_resetpassword($code)
	{
		$this->datas['activationcode'] = $code;
		$template = $this->file->read(APP_BASE_URL."templates/reset-password.html");
		foreach($this->datas as $key => $value)
		{
			$template = str_replace('{{'.$key.'}}', $value, $template);
		}
		return $template;
	}

	/**gets the newsletter email template for use when sending emails
	 * @param null
	 * @return string
	 */
	public function get_newsletter()
	{
		
	}

	/**gets the event invite email template for use when sending emails
	 * @param null
	 * @return string
	 */
	public function get_eventinvite()
	{

	}

	/**gets the account close email template for use when sending emails
	 * @param null
	 * @return string
	 */
	public function get_accountclose()
	{

	}

}