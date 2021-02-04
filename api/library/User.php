<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author dsaved
 */
class User {
    //put your code here
    
	private static $_instance = null;
	private static $table = "users";
	private static $username = "username";
	private static $user_id = "id";
	private static $email = "email";
	private static $phone = "phone";

	private $_db,$_string,
			$_data,
			$_permKey;
	public	$_isLoggedIn;
 
  	public function __construct($auth = false){
	   $this->_db = DB::getInstance();
	   $this->_string = Strings::getInstance();
   }

   public static function getInstance(){
	   if(!isset(self::$_instance)){
		   self::$_instance = new User();
	   }
	   return self::$_instance;
   }
           
   	// Delete user by id
	public function delete($id){
		if(!$this->_db->delete(self::$table, array(self::$user_id,'=',$id))){
			return false;	
		}else{
			return true;
		}
	}
	
	public function create($fields = array() ){
		if(!$this->_db->insert(self::$table, $fields)){
			return false;
		}else{
			return true;
		}
		
	}

	public function getId(){
		if ($this->_db->lastInsertId()) {
			return $this->_db->lastInsertId();
		} else {
			return null;
		}
	}
	
	public function update($fields , $id){
		if($this->find_unknown($id)){
			$id = $this->_data->id;
			if($this->_db->updateByID(self::$table,self::$user_id,$id, $fields)){
				return true;
			}else{
				return false;
			}	
		}else{
			if($this->_db->updateByID(self::$table,self::$user_id,$id, $fields)){
				return true;
			}else{
				return false;
			}	
		}
	}
	

	public function find($user = null,$field=null, $clean = true){
		if($user){
			if($field===null){
				if (is_numeric($user)) {
					$field=self::$user_id;
				} elseif(strpos($user, "@")!=FALSE){
					$field= self::$email;
					$user = "$user";
				}else {
					$field= self::$phone;
					$user = "$user";
				}
			}
			$data  = $this->_db->get(self::$table, array($field, '=', $user));

			if($data->count){
				$this->_data = $data->first();
				if($clean){
					$this->cleanData();
				}
				return true;
			}else{
				if($field===self::$phone){
					if (is_numeric($user)) {
						$field=self::$phone;
					}
					$user = "$user";
				}

				$data  = $this->_db->get(self::$table, array($field, '=', $user));
				if($data->count){
					$this->_data = $data->first();
					if($clean){
						$this->cleanData();
					}
					return true;  
				}else{
					$data  = $this->_db->get(self::$table, array(self::$username, '=', $user));
					if($data->count){
						$this->_data = $data->first();
						if($clean){
							$this->cleanData();
						}
						return true;  
					}
				}// End cross checking
			}
	     }
		return false;
	}

	public function find_unknown($user, $clean = true){
		if($user){
            $id = $user;
			if(!is_numeric($user)){
                $id=0;
			}
			$data  = $this->_db->query("SELECT * FROM ".self::$table. " WHERE (".self::$user_id."=$id OR ".self::$email."='$user' OR ".self::$phone."='$user' OR ".self::$username."='$user')");

			if($data->count){
				$this->_data = $data->first();
				if($clean){
					$this->cleanData();
				}
				return true;
			}else{
				return false;
			}
	     }
		return false;
	}
	
	public function data(){
		return $this->_data;
	}

	public function cleanData(){
		unset($this->_data->salt);
		unset($this->_data->uni_salt);
		unset($this->_data->password);
		unset($this->_data->activated);
	}
	
	public function login($identity = null, $password = null,$field='' ,$remember = false){
		
	  if(empty($identity) && empty($password) && $this->exists()){
			$this->_isLoggedIn = true;
	  }else{
	  $user = $this->find_unknown($identity, false);
	  if($user){
		 if($password === Hash::decrypt($this->data()->password, $this->data()->salt))  {
				$this->_isLoggedIn = true;
				$this->cleanData();
		  		return true;
		   }
	     }else{return false;}
	   }
	   return false;
	}
	
	public function exists(){
	  return (!empty($this->_data))? true: false;	
	}

	public function getpassword($param)
	{
		$password = null;
		$usr = $this->find_unknown($param, false);
		if ($usr) {
			$password = Hash::decrypt($this->data()->password, $this->data()->salt);
		}
		return $password;
	}
}