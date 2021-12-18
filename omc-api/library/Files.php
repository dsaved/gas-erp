<?php 

class Files{

	private static $_instance = null;
	private $_location = UPLOADS_ADDR;
	private $_imagelocation = IMAGE_ADDR;
	private $_audiolocation = AUDIO_ADDR;
	private $_videolocation = VIDEO_ADDR;
	private $_profileimages = PROFILEIMAGE_ADDR;
	private $_filelocation = FILE_ADDR;
	private $_docxlocation = DOCX_ADDR;
	private $temp = TEMP_ADDR;
	private $_loglocation = LOG_ADDR;
	private $_logbackuplocation = LOG_BKP_ADDR;
	private $zip;

	private $_allfiles = array();

	private $_data = '';
	private $site_url = '';
	private $base_url = '';
	private $_bytes = 0;

	public $video_format = array('mpg','mpeg','avi','wmv','mov','rm','ram','swf','flv','webm','ogg','mp4','mkv','vlc');
	public $audio_format = array('acc','midi','mid','rm','wav','mp3','m4a');
	public $image_format = array('webp','bmp','jpg','jpeg','png','gif','svg','png');
	public $all_format = array('zip','csv','rar','pdf','docx','doc','txt','xls','xlsx','mpg','mpeg','avi','wmv','mov','rm','ram','swf','flv','webm','ogg','mp4','mkv','vlc','webp','bmp','jpg','jpeg','png','gif','svg','acc','midi','mid','wma','wav','mp3','webp','bmp','jpg','jpeg','png','gif','svg','m4a');
	public $files_format = array('pdf','docx','doc','txt','xls','xlsx','csv');
	public $other = array('zip','rar');

	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new Files();
		}
		return self::$_instance;
	}

	function __construct()
	{
		$this->base_url = BASE_URL;
		$this->site_url = SITE_LINK;
		$this->zip = new ZipArchive();
	}

	/*
	*set file location to write to
	*/
	public function set_location($value)
	{
		$this->_location = "$value/";
		
	}

	/*
	*set file data to write
	*/
	public function set_data($data)
	{
		$this->_data = $data;
		
	}

	/*
	*get profile image directory
	*/
	public function get_location()
	{
		return $this->_location;
	}

	/*
	*get profile image directory
	*/
	public function get_profileDirectory()
	{
		return $this->_profileimages;
	}

	/*
	*get file directoryn through the file extension
	*/
	public function get_directory($value)
	{
		$value = strtolower($value);
		if (in_array($value, $this->image_format)) {
			 $here = $this->_imagelocation;
		}else if (in_array($value, $this->audio_format)) {
			 $here = $this->_audiolocation;
		}else if (in_array($value, $this->video_format)) {
			 $here = $this->_videolocation;
		}else if (in_array($value, $this->files_format)) {
			 $here = $this->_docxlocation;
		}else{
			 $here = $this->_filelocation;
		}
		return $here;
	}

	/*
	*get file extenssion through the file name
	*/
	public function get_file_info($value)
	{
		$path_parts = pathinfo($value);
		return array(
			'dirname' => $path_parts['dirname'], 
			'basename' => $path_parts['basename'], 
			'extension' => $path_parts['extension'], 
			'filename' => $path_parts['filename']
		);
	}

	/*
	*get file extenssion through the file name in the sameserver
	*/
	public function get_onlinfile_info($value)
	{
		
		if (file_exists($value)) {
		    $path_parts = pathinfo($value);
			return array(
				'dirname' => $path_parts['dirname'], 
				'basename' => $path_parts['basename'], 
				'extension' => $path_parts['extension'], 
				'filename' => $path_parts['filename']
			);
		}else{
			return array(
				'dirname' => "", 
				'basename' => "", 
				'extension' => "", 
				'filename' => ""
			);
		}
	}

	/*
	*get file types for given request
	*/
	public function get_file_type($value,$ext='')
	{

		$file_typed = array();
		switch ($value) {
			case 'image':
			 	$file_typed = $this->image_format;
				break;
			
			case 'audio':
			 	$file_typed = $this->audio_format;
				break;
			
			case 'video':
			 	$file_typed = $this->video_format;
				break;
			
			case 'docs':
			case 'documents':
			case 'document':
			case 'doc':
			case 'file':
			case 'files':
				$file_typed = $this->files_format;
				break;
			case 'all':
				$index = count($this->all_format);
				$this->all_format[$index] = strtolower($ext);
			 	$file_typed = $this->all_format;
				break;
			
			default:
				$index = count($this->all_format);
				$this->all_format[$index] = strtolower($ext);
			 	$file_typed = $this->all_format;
				break;
		}
		return $file_typed;
	}
	
	/*
	*read files contents
	*@params file the location of file to read
	*/
	public function read($file)
	{
		$myfile = fopen($file, "r") or die("Unable to open file!");
		$data = fread($myfile,filesize($file));
		fclose($myfile);
		return $data;
	}
		
	/*
	write to log file
	*/
	public function writeOrCreateFile($dir,$filename, $text)
	{
        if(!file_exists($dir.$filename)){
            $this->create_file($dir, $filename);
        }
		return file_put_contents($dir.$filename, $text.PHP_EOL , FILE_APPEND | LOCK_EX);
	}
	
	/*
	move files from one location to another folder if time is passed
	*/
	public function moveLogs()
	{
        $files = $this->getFiles($this->_loglocation);
        foreach ($files as $file) {
            $fileInfo = $this->get_file_info($file);
            $filename = $fileInfo["filename"];
            $fileextenssion = ".".$fileInfo["extension"];
            $time = explode("-", $fileInfo["filename"])[1];
            $this->creat_directory($this->_logbackuplocation);
            if((int)$time < (int)date("Ymd")){
                $this->move_file($this->_loglocation.$filename.$fileextenssion,$this->_logbackuplocation.$filename.$fileextenssion);
            }
        }
	}

	/*
	move files from one location to another
	*/
	public function move_file($from,$to)
	{
		return rename($from, $to);
	}
	
	/*
	copy fils from one location to another
	*/
	public function copy_file($from,$to)
	{
		return copy($from, $to);
	}
	
	/*
	move fils uploaded to desired directory
	*/
	public function upload_files($temp,$location)
	{
		$array_temp = explode('/', $location);
		$removed_file_name = array_slice($array_temp,0,-1);
		$location_to_create = implode('/', $removed_file_name);
		$this->creat_directory($location_to_create);
		return move_uploaded_file($temp, $location);
	}
	
	/*
	copy all fils from a location to another directory
	*/
	public function copy_all_file($from,$destination)
	{
		// Get array of all source files
		$files = scandir($from);
		// Identify directories
		$source = "$from/";
		$destination = "$destination/";
		// make the directory if it does not exist
		$this->creat_directory($destination);
		// Cycle through all source files
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
				// If we copied this successfully, mark it for deletion
			$done = copy($source.$file, $destination.$file);
		}
		return $done;
	}
	
	/*
	move all fils from a location to another directory
	*/
	public function move_all_file($from,$destination)
	{
		// Get array of all source files
		$files = scandir($from);
		// Identify directories
		$source = "$from/";
		$destination = "$destination/";
		// make the directory if it does not exist
		$this->creat_directory($destination);
		// Cycle through all source files
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
				// If we copied this successfully, mark it for deletion
			$done = rename($source.$file, $destination.$file);
		}
		return $done;
	}

	/*
	check wether loction file is a dire
	*/

	public function is_directory ($file)
	{ 
		return is_dir($file);
	}

	public function getFiles($location)
	{
		if ($handle = opendir($location)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && strpos($entry, '.php') === false && !$this->is_directory($location.$entry)  && strpos($entry, '.db') === false) {
				     $this->_allfiles[] =  $entry;
				}
			}
			closedir($handle);
		}

		return $this->_allfiles;
	}

	public function getFolders($location)
	{
		if ($handle = opendir($location)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && $this->is_directory($location.$entry)) {
				     $this->_allfiles[] = $entry;
				}
			}
			closedir($handle);
		}
		return $this->_allfiles;
	}

	public function countFiles($location)
	{
		if ($handle = opendir($location)) {
			$count = 0;
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && strpos($entry, '.php') === false && !$this->is_directory($location.$entry)  && strpos($entry, '.db') === false) {
				     $count++;
				}
			}
			closedir($handle);
		}

		return $count;
	}

	/*
	Delete file
	*/
	public function delete_file($file)
	{
		if (is_array($file)) {
			foreach ($file as $value) {
				if (strpos($value, 'http') !== false || strpos($value, 'https') !== false) {
					$value = DB::getInstance()->replace($this->site_url,$this->base_url,$value);
				}
				if(file_exists($value)){
			    	$result = @unlink($value);
				}else{
					$result = false;
				}
			}
			return $result;
		}else{
			if (strpos($file, 'http') !== false || strpos($file, 'https') !== false) {
				$file = DB::getInstance()->replace($this->site_url,$this->base_url,$file);
			}
			if(file_exists($file)){
			    return @unlink($file);
			}else{
			    return false;
			}
		}
	}

	/*
	Delete all files
	*/
	public function delete_all_file($value)
	{
		// Get array of all source files
		$files = scandir($value);
		foreach ($files as $file) {
  			$done = unlink($file);
		}
  		return $done;
	}

	/*
	Create directory if it is not existing
	*/
	public function creat_directory($value)
	{
		if (!file_exists($value)) {
		    mkdir($value, 0777, true);
		    $this->create_file($value,'');
		}
	}

	/*
	*rename file
	*/
	public function rename($oldname, $newname)
	{
		$ext1 = $this->get_file_info($oldname)['extension'];
		$ext2 = $this->get_file_info($newname)['extension'];
		$oldname = $this->get_directory($ext1).$oldname;
		$newname = $this->get_directory($ext2).$newname;
		$oldname = $this->base_url.$this->_location.$oldname;
		$newname = $this->base_url.$this->_location.$newname;
		if (rename($oldname, $newname)) {
		    return true;
		}
		return false;
	}

	/*
	Create file if it is not existing
	*/
	public function update_file($location,$file_name)
	{
		$file = $location.'/'.$file_name;
		if (file_exists($file)) {
		    $this->delete_file($file);
		    $this->create_file($location,$file_name);
		}else{
			$this->create_file($location,$file_name);
		}
	}

	/*
	Create file if it is not existing
	*/
	public function create_file($location,$file_name)
	{
		$file = $location.'/'.$file_name;
		$this->creat_directory($location);
		if (!file_exists($file)) {
		    $fp = fopen($file, 'w');
			fwrite($fp, $this->_data);
			fclose($fp);
		}
	}

	public function upload_file($value,$type=null)
	{
		$result = array();
		$file_type = array();
		$uploaded = array();
		foreach($value['file']['error'] as $key => $error)
		{
			$file_type = $this->get_file_type($type);
			$ext = $this->get_file_info($value['file']['name'][$key])['extension'];
			if ($error == UPLOAD_ERR_OK ) {
				if (in_array(strtolower($ext), $file_type) ) {
					$file_name =  date("Ymd").str_replace(" ","-",$key.$value['file']['name'][$key]);
					$file_size =  $value['file']['size'][$key];
					$file_tmp  =  $value['file']['tmp_name'][$key];
					$file_type =  $value['file']['type'][$key];  
					$name      =  rand('0','9999').'_'.$file_name;
					$directory =  $this->get_directory($ext);
					$this->upload_files($file_tmp, $this->base_url.$this->_location.$directory.$name);
					$uploaded[] = $this->base_url.$this->_location.$directory.$name;
					$result['name'] = $name;
					$result['location'] = $this->site_url.$this->_location.$directory.$name;
					$result['base_location'] = $uploaded[0];
				}else{
					if (!empty($uploaded)) {
				       $this->delete_file($uploaded);
				    }
				    $result[0] ='failed';
				    $result[1] = $value['file']['name'][$key].'File type is not allowed here';
					return $result;
					exit();
				}
			}else{
				if (!empty($uploaded)) {
			       $this->delete_file($uploaded);
			    }
			    $result[0] ='failed';
			    $result[1] ='Error uploading file '.$value['file']['name'][$key];
				return $result;
				exit();
			}
		}
		return $result;
	}

	public function upload_multiple_file($value,$type=null)
	{
		$result = array();
		$file_type = array();
		$uploaded = array();
		$result['location'] = array();
		$result['base_location'] = array();
		$result['relative'] = array();
		$result['file_name'] = array();
		foreach($value['file']['error'] as $key => $error)
		{
			$file_type = $this->get_file_type($type);
			$ext = $this->get_file_info($value['file']['name'][$key])['extension'];
			if ($error == UPLOAD_ERR_OK ) {
				if (in_array(strtolower($ext), $file_type) ) {
					$file_name =   date("Ymd").str_replace(" ","-",$key.$value['file']['name'][$key]);
					$file_size =  $value['file']['size'][$key];
					$file_tmp  =  $value['file']['tmp_name'][$key];
					$file_type =  $value['file']['type'][$key];
					$name      =  rand('0','9999').'_'.$file_name;
					$directory =  $this->get_directory($ext);
					$this->upload_files($file_tmp, $this->base_url.$this->_location.$directory.$name);
					$result['success'] = true;
					array_push($result['base_location'], $this->base_url.$this->_location.$directory.$name);
					array_push($result['location'], $this->site_url.$this->_location.$directory.$name);
					array_push($result['relative'], $this->_location.$directory.$name);
					array_push($result['file_name'], $name);
				}else{
					if (!empty($result['location'])) {
				       $this->delete_file($result['location']);
				    }
				    $result['success'] = false;
				    $result['message'] = $value['file']['name'][$key].'File type is not allowed here';
					return $result;
					exit();
				}
			}else{
				if (!empty($result['location'])) {
			       $this->delete_file($result['location']);
			    }
				$result['success'] = false;
			    $result['message'] ='Error uploading file '.$value['file']['name'][$key];
				return $result;
				exit();
			}
		}
		return $result;
	}

	public function upload_profileImage($value,$type=null)
	{
		$result = array();
		$file_type = array();
		$uploaded = array();

			$file_type = $this->get_file_type($type);
			$ext = $this->get_file_info($value['file']['name'])['extension'];
			if ($value['file']['error'] == UPLOAD_ERR_OK ) {
				if (in_array(strtolower($ext), $file_type) ) {
					$file_name =   date("Ymd").str_replace(" ","-",$value['file']['name']);
					$file_size =  $value['file']['size'];
					$file_tmp  =  $value['file']['tmp_name'];
					$file_type =  $value['file']['type'];  
					$name      =  rand('0','9999').'_'.$file_name;
					$directory =  $this->get_profileDirectory();
					$this->upload_files($file_tmp, $this->base_url.$this->_location.$directory.$name);
					$uploaded[] = $this->base_url.$this->_location.$directory.$name;
					$result['success'] = true;
					$result['file_name'] = $name;
					$result['location'] = $this->site_url.$this->_location.$directory.$name;
					$result['base_location'] = $uploaded[0];
					$result['relative'] = $this->_location.$directory.$name;
				}else{
					if (!empty($uploaded)) {
				       $this->delete_file($uploaded);
				    }
				    $result['success'] =false;
				    $result['message'] = $value['file']['name'].'File type is not allowed here';
					return $result;
				}
			}else{
			    $result['success'] =false;
			    $result['message'] ='Error uploading file '.$value['file']['name'];
				return $result;
			}
		return $result;
	}

	public function upload_singleFile($value,$type=null)
	{
		$result = array();
		$filetype = array();
		$uploaded = array();

			$filetype = $this->get_file_type($type);
			$ext = $this->get_file_info($value['file']['name'])['extension'];
			if ($value['file']['error'] == UPLOAD_ERR_OK ) {
				if (in_array(strtolower($ext), $filetype) ) {
                    $file_name =   date("Ymd").str_replace(" ", "-", $value['file']['name']);
					$file_size =  $value['file']['size'];
					$file_tmp  =  $value['file']['tmp_name'];
					$file_type =  $value['file']['type'];
					$extn = $this->get_file_info($file_name)['extension'];
					$fname = $this->get_file_info($file_name)['filename'];
					$name      =  $fname.rand('0','99').".".$extn;
					$directory =  $this->get_directory($ext);
					$this->upload_files($file_tmp, $this->base_url.$this->_location.$directory.$name);
					$uploaded[] = $this->base_url.$this->_location.$directory.$name;
					$link[] = $this->site_url.$this->_location.$directory.$name;
					$result['relative'] =  $this->_location.$directory.$name;
					$result['success'] = true;
					$result['file_name'] = $name;
					$result['location'] = $link[0];
					$result['base_location'] = $uploaded[0];
				}else{
					if (!empty($uploaded)) {
				       $this->delete_file($uploaded);
				    }
				    $result['success'] =false;
				    $result['message'] = $value['file']['name'].' File type('.$ext.') is not allowed here';
					return $result;
				}
			}else{
			    $result['success'] =false;
			    $result['message'] ='Error uploading file '.$value['file']['name'];
				return $result;
			}
		return $result;
	}

	public function upload($value,$filed,$type=null)
	{
		$result = array();
		$filetype = array();
		$uploaded = array();

			$filetype = $this->get_file_type($type);
			$ext = $this->get_file_info($value[$filed]['name'])['extension'];
			if ($value[$filed]['error'] == UPLOAD_ERR_OK ) {
				if (in_array(strtolower($ext), $filetype) ) {
                    $file_name =   date("Ymd").str_replace(" ", "-", $value[$filed]['name']);
					$file_size =  $value[$filed]['size'];
					$file_tmp  =  $value[$filed]['tmp_name'];
					$file_type =  $value[$filed]['type'];
					$extn = $this->get_file_info($file_name)['extension'];
					$fname = $this->get_file_info($file_name)['filename'];
					$name      =  $fname.rand('0','99').".".$extn;
					$directory =  $this->get_directory($ext);
					$this->upload_files($file_tmp, $this->base_url.$this->_location.$directory.$name);
					$uploaded[] = $this->base_url.$this->_location.$directory.$name;
					$link[] = $this->site_url.$this->_location.$directory.$name;
					$result['relative'] =  $this->_location.$directory.$name;
					$result['success'] = true;
					$result['file_name'] = $name;
					$result['location'] = $link[0];
					$result['base_location'] = $uploaded[0];
				}else{
					if (!empty($uploaded)) {
				       $this->delete_file($uploaded);
				    }
				    $result['success'] =false;
				    $result['message'] = $value[$filed]['name'].' File type('.$ext.') is not allowed here';
					return $result;
				}
			}else{
			    $result['success'] =false;
			    $result['message'] ='Error uploading file '.$value[$filed]['name'];
				return $result;
			}
		return $result;
	}

    public function upload_file_to($files,$filed,$filename,$desired_dir){
        $file_name =  date("Ymd").$files[$filed]['name'];
		$file_tmp = $files[$filed]['tmp_name'];
		$image = $filename;
		if(empty($errors)==true){
			$this->creat_directory($this->base_url.$desired_dir);
			if(is_dir($this->base_url.$desired_dir.$image)==false){
				move_uploaded_file($file_tmp,$this->base_url.$desired_dir.$image);
			}else{
                $this->delete_file($this->base_url.$desired_dir.$image);
				move_uploaded_file($file_tmp,$this->base_url.$desired_dir.$image);
            }
        }
        return $desired_dir.$image;
	}
	
	public function size_($link){
		if (strpos($link, 'http') !== false || strpos($link, 'https') !== false) {
			$link = DB::getInstance()->replace($this->site_url,'',$link);
		}
		$bytes = filesize($link);
		return $this->size_format($bytes);
	}

	public function size_format($bytes)
	{
		if ($bytes >= 1125899906842620) {
			$this->_bytes = number_format($bytes / 1125899906842620,2).' PB';
		}else if ($bytes >= 10995511627776) {
			$this->_bytes = number_format($bytes / 10995511627776,2).' TB';
		}else if ($bytes >= 1073741824) {
			$this->_bytes = number_format($bytes / 1073741824,2).' GB';
		}else if ($bytes >= 1048576) {
			$this->_bytes = number_format($bytes / 1048576,2).' MB';
		}else if ($bytes >= 1024) {
			$this->_bytes = number_format($bytes / 1024,2).' KB';
		}else if ($bytes > 1) {
			$this->_bytes = $bytes.' Bytes';
		}else if ($bytes == 1) {
			$this->_bytes = $bytes.' Byte';
		}else {
			$this->_bytes .= ' Bytes';
		}

		return $this->_bytes;
	}

	public function getLatestFiles($dir,$limit)
	{
		// getting files with specified four extensions in $files
		$ext = implode(',', $this->all_format);
		$ext = "*.{".$ext."}";
		$files = glob($dir.$ext, GLOB_BRACE);
		// will get filename and filetime in $files
		$files = array_combine($files, array_map("filemtime", $files));
		// will sort files according to the values, that is "filetime"
		arsort($files);
		// we don't require time for now, so will get only filenames(which are as keys of array)
		$files = array_keys($files);
		$starting_index = 0;
		// will limit the resulted array as per our requirement
		$files = array_slice($files, $starting_index,$limit);
		// will print the final array
		return $files;
	}

	public function zip($files){
		$zipname = $this->base_url.$this->temp.time().'.zip';
		$this->zip->open($zipname, ZipArchive::CREATE);
		foreach ($files as $file) {
			if (strpos($file, 'http') !== false || strpos($file, 'https') !== false) {
				$file = DB::getInstance()->replace(SITE_LINK.SUB_FOLDER,'',$file);
			}
			$this->zip->addFile($file);
		}
		$this->zip->close();
		return $zipname;
	}

	public function unzip($zipfile,$extract_to){
		$res = $this->zip->open($zipfile);
		if ($res === TRUE) {
			$zip->extractTo($extract_to);
			$zip->close();
			return true;
		} else {
			return false;
		}
	}
}
?>