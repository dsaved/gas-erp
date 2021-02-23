<?php
defined('APP') or exit('No direct script access allowed');

/**
* Application Base Controller 
* Controllers Which Do Not Need Authentication Inherit From This Class
*/
class BaseController
{
	function __construct()
	{
        $this->db = new DB();
        $this->http = new Http();
        $this->image = new Image();
		// $this->checkRequest();
        logData();
	}

	public function loadModel($file)
	{
		$path = MODELS . $file . "Model.php";
		if (file_exists($path)) {
			$modelName = $file . "Model";
			$this->model = new $modelName;
		}else{
			if ($handle = opendir(MODELS)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != ".." && is_dir(MODELS.$entry)) {
						$filename = MODELS ."$entry/". $file . "Model.php";
						if (file_exists($filename)) {
							$modelName = $file . "Model";
							$this->model = new $modelName;
							break;
						}
					}
				}
				closedir($handle);
			}
		}
	}
	
    public function checkRequest(){
        if(!isset($this->http->json) || empty($this->http->json)){
			if(!isset($this->http->files) || empty($this->http->files)){
				$this->http->_403();
			}
        }
	}
}
