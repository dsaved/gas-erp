<?php
class UsersModel extends BaseModel
{
    private static $authentication = "authentication";

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getUsers(" WHERE `user_type` NOT IN ('bdc','omc')");
    }

    public function get()
    {
        $id = $this->http->json->id??null;
        if(empty($id)){
            $this->http->_403("User id is required");
        }
        return $this->getSingleUsers("WHERE `id`=$id AND `user_type` NOT IN ('bdc','omc') ");
    }

    public function profile()
    {
        $id = $this->http->json->id??null;
        if(empty($id)){
            $this->http->_403("User id is required");
        }
        return $this->getSingleUsers("WHERE `id`=$id");
    }
    
    public function options()
    {
        $response = array();
        $response['roles'] = array();
        $response['organizations'] = array();
        $this->db->query("SELECT id,name FROM `organizations` ORDER BY `name`");
        if ($this->db->results() && $this->db->count > 0) {
            foreach ($this->db->results() as $data) {
                $accounts = array();
                $accounts['value'] = $data->id;
                $accounts['label'] = $data->name;
                array_push($response['organizations'], $accounts);
            }
        }

        $this->db->query("SELECT id,role FROM `users_role` ORDER BY `role`");
        if ($this->db->results() && $this->db->count > 0) {
            foreach ($this->db->results() as $data) {
                $role = array();
                $role['value'] = $data->id;
                $role['label'] = $data->role;
                array_push($response['roles'], $role);
            }
        }
        return $response;
    }

    public function get_roles()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $condition = "";
        if($search){
            $condition = "WHERE (`role` LIKE '%$search%') ";
        }
        $this->paging->table("`users_role`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `role`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["roles"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No roles available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function get_role()
    {
        $response = array();
        $id = $this->http->json->id;
        $this->db->query("SELECT * FROM users_role WHERE id = $id LIMIT 1");
        $result = $this->db->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["role"] = $this->db->first();
        } else {
            $response['success'] = false;
            $response['message'] = "No roles available";
        }
        return $response;
    }

    public function role_add()
    {
        $role = trim($this->http->json->role??null);
        if($role===null){
            $this->http->_403("Please provide role name");
        }
        $permissions = $this->http->json->permissions??null;
        if($permissions===null){
            $this->http->_403("Please choose permissions");
        }
        
        $data = array(
            'role' =>  $role,
            'permissions' => implode(",",$permissions)
        );
        if($this->db->insert("users_role",$data)){
            $response['success'] = true;
            $response['message'] = "Role has been created";
            return $response;
        }else {
            $this->http->_500("Cannot create role");
        }
    }

    public function role_update()
    {
        $id = $this->http->json->id;
        $role = trim($this->http->json->role??null);
        if($role===null){
            $this->http->_403("Please provide role name");
        }
        $permissions = $this->http->json->permissions??null;
        if($permissions===null){
            $this->http->_403("Please choose permissions");
        }
        
        $data = array(
            'role' =>  $role,
            'permissions' => implode(",",$permissions)
        );
        if($this->db->updateByID("users_role","id",$id,$data)){
            $response['success'] = true;
            $response['message'] = "Role has been updated";
            return $response;
        }else {
            $this->http->_500("Cannot update role");
        }
    }

    public function role_delete()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `users_role` WHERE `id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Role deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Role could not be deleted ";
        }
        return $response;
    }

    public function omc(){
        $id = $this->http->json->id??null;
        $other = "";
        if(!empty($id)){
            $other = " AND `id`=$id ";
            return $this->getSingleUsers(" WHERE `user_type`='omc' $other");
        }else{
            return $this->getUsers(" WHERE `user_type`='omc' $other");
        }
    }

    public function bdc(){
        $id = $this->http->json->id??null;
        $other = "";
        if(!empty($id)){
            $other = " AND `id`=$id ";
            return $this->getSingleUsers(" WHERE `user_type`='bdc' $other");
        }else{
            return $this->getUsers(" WHERE `user_type`='bdc' $other");
        }
    }

    public function getUsers($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if($search){
            if (empty($condition)) {
                $condition = " WHERE (`username` LIKE '%$search%' OR `fullname` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%' ) ";
            }else{
                $condition .= " AND (`username` LIKE '%$search%' OR `fullname` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%' ) ";
            }
        }
        $this->paging->rawQuery("SELECT *,(SELECT role FROM `users_role` WHERE `id`=users.role LIMIT 1) as role FROM `users` $condition Order By `fullname`");
        // $this->paging->rawQuery("SELECT *,(SELECT * FROM `users_role` WHERE `id`={$value->role} LIMIT 1) as role, (SELECT name FROM `omc` WHERE `id`={$value->organization} LIMIT 1) as omc_org, (SELECT * FROM `organizations` WHERE `id`={$value->organization} LIMIT 1) as organization,  FROM `users` $condition Order By `fullname`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["users"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_users;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function getSingleUsers($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if($search){
            if (empty($condition)) {
                $condition = " WHERE (`username` LIKE '%$search%' OR `fullname` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%' ) ";
            }else{
                $condition = " AND (`username` LIKE '%$search%' OR `fullname` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%' ) ";
            }
        }
        $this->paging->table("`users`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `fullname`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $password = Hash::decrypt($value->password, $value->salt);
                $value->password = $password;
                $value->confirm_password = $password;
                unset($value->salt);
                if($value->access_level=="user"){
                    $this->db->query("SELECT * FROM `organizations` WHERE `id`={$value->organization} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $organization = $this->db->first();
                        $value->organization = array("value"=>$organization->id,"label"=>$organization->name);
                    }else{
                        $value->organization = [];
                    }
                }elseif($value->user_type=="omc"){
                    $this->db->query("SELECT * FROM `omc` WHERE `id`={$value->organization} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $organization = $this->db->first();
                        $value->organization = array("value"=>$organization->id,"label"=>$organization->name);
                    }else{
                        $value->organization = [];
                    }
                }
                
                $this->db->query("SELECT * FROM `users_role` WHERE `id`={$value->role} LIMIT 1");
                if($this->db->results() && $this->db->count >0){
                    $role = $this->db->first();
                    $value->role = array("value"=>$role->id,"label"=>$role->role, "permissions"=>$role->permissions);
                }else{
                    $value->role = null;
                }
                
                $this->db->query("SELECT * FROM `users_page` WHERE `user_id`={$value->id} LIMIT 1");
                if($this->db->results() && $this->db->count >0){
                    $pages = $this->db->first();
                    $value->selectedPages = explode(",", $pages->pages);
                }else{
                    $value->selectedPages = [];
                }
            }
            $response["users"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_users;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function create()
    {
        $pages = implode(",", $this->http->json->pages)??"";

        $photo = trim($this->http->json->photo??null);
        $organization = trim($this->http->json->organization??null);
        $username = trim($this->http->json->username??null);
        if($username===null){
            $this->http->_403(Msg::$provide_username);
        }

        $tin = $this->http->json->tin??null;
        $location = $this->http->json->location??null;
        $designation = $this->http->json->designation??null;
        $region = $this->http->json->region??null;
        $district = $this->http->json->district??null;
        
        $fullname = $this->http->json->fullname??null;
        if($fullname===null){
            $this->http->_403(Msg::$provide_first_name);
        }

        $phone = trim($this->http->json->phone??null);
        // if(!$phone){
        //     $this->http->_403(Msg::$provide_phone);
        // }

        $email = trim($this->http->json->email??null);
        // if(!$email){
        //     $this->http->_403(Msg::$provide_email);
        // }

        $user_type = $this->http->json->user_type??null;
        if($user_type===null){
            $this->http->_403("Please select user type");
        }

        $access_level = $this->http->json->access_level??null;
        if($access_level===null){
            $this->http->_403(Msg::$please_choose_access_level);
        }

        $baseurl = $this->http->json->baseurl??null;
        if($baseurl===null){
            $this->http->_403("Please select user base URL");
        }

        $role = $this->http->json->role??null;
        if($role===null){
            $this->http->_403("Please select user role");
        }

        $password = $this->http->json->password??null;
        if(!$password){
            $this->http->_403(Msg::$provide_password);
        }

        if(strlen($password)<4){
            $this->http->_403(Msg::$incorrect_password_length);
        }

        if(empty($username)){
            $username = explode("@", $email)[0];
        }

        //check if user with following information already exits in the system
        if($this->user->find($username,"username")){
            $this->http->_403(Msg::username_exist($username));
        }
        if($this->user->find($email,"email")){
            $this->http->_403(Msg::email_exist($email));
        }
        if($this->user->find($phone,"phone")){
            $this->http->_403(Msg::phone_exist($phone));
        }

        if(!empty($tin)){
            $this->db->query("SELECT * FROM `users` WHERE `tin` = '$tin'");
            if($this->db->results() && $this->db->count >0){
                $this->http->_403("This TIN ($tin) already exist");
            }
        }
        
        $salt = Hash::salt($email);
        $aes256Key = Hash::make($salt,32);//store this key to db as salt
        $encryptedpassword = Hash::encrypt($password,$aes256Key);

        $data = array(
            'username' =>  $username,
            'email' => $email,
            'fullname' => $fullname,
            'phone' => $phone,
            'salt' =>  $aes256Key,
            'photo' =>  $photo,
            'role' =>  $role,
            'tin' =>  $tin,
            'location' =>  $location,
            'designation' =>  $designation,
            'region' =>  $region,
            'district' =>  $district,
            'user_type' =>  $user_type,
            'baseurl' =>  $baseurl,
            'organization' =>  $organization,
            'access_level' =>  $access_level,
            'password' => $encryptedpassword,
            );

        try{
            if($this->user->create($data)){
                $this->db->insert("users_page", array("user_id"=>$this->user->getId(),"pages"=>$pages));
                $response['success'] = true;
                $response['message'] = "Account has been created";
                return $response;
            }else {
                $this->http->_500(Msg::$account_create_error);
            }
        }catch(Exception $ex){
            $this->http->_500($ex->getMessage());
        }

    }
    
    public function update()
    {
        $id = $this->http->json->id;
        $pages = implode(",", $this->http->json->pages)??"";

        $photo = trim($this->http->json->photo??null);
        $organization = trim($this->http->json->organization??null);
        $username = trim($this->http->json->username??null);
        if($username===null){
            $this->http->_403(Msg::$provide_username);
        }

        $tin = $this->http->json->tin??null;
        $location = $this->http->json->location??null;
        $designation = $this->http->json->designation??null;
        $region = $this->http->json->region??null;
        $district = $this->http->json->district??null;

        $fullname = $this->http->json->fullname??null;
        if($fullname===null){
            $this->http->_403(Msg::$provide_first_name);
        }

        $phone = trim($this->http->json->phone??null);
        // if(!$phone){
        //     $this->http->_403(Msg::$provide_phone);
        // }

        $email = trim($this->http->json->email??null);
        // if(!$email){
        //     $this->http->_403(Msg::$provide_email);
        // }

        $user_type = $this->http->json->user_type??null;
        if($user_type===null){
            $this->http->_403("Please select user type");
        }

        $access_level = $this->http->json->access_level??null;
        if($access_level===null){
            $this->http->_403(Msg::$please_choose_access_level);
        }

        $baseurl = $this->http->json->baseurl??null;
        if($baseurl===null){
            $this->http->_403("Please select user base URL");
        }

        $role = $this->http->json->role??null;
        if($role===null){
            $this->http->_403("Please select user role");
        }

        $password = $this->http->json->password??null;
        if(!$password){
            $this->http->_403(Msg::$provide_password);
        }

        if(strlen($password)<4){
            $this->http->_403(Msg::$incorrect_password_length);
        }

        if(empty($username)){
            $username = explode("@", $email)[0];
        }

        //check if user with following information already exits in the system
        $this->db->query("SELECT * FROM `users` WHERE `username` = '$username' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::username_exist($username));
        }
        $this->db->query("SELECT * FROM `users` WHERE `email` = '$email' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::email_exist($email));
        }
        $this->db->query("SELECT * FROM `users` WHERE `phone` = '$phone' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::phone_exist($phone));
        }

        if(!empty($tin)){
            $this->db->query("SELECT * FROM `users` WHERE `tin` = '$tin' AND `id`!=$id");
            if($this->db->results() && $this->db->count >0){
                $this->http->_403("This TIN ($tin) already exist");
            }
        }

        $salt = Hash::salt($email);
        $aes256Key = Hash::make($salt,32);//store this key to db as salt
        $encryptedpassword = Hash::encrypt($password,$aes256Key);

        $data = array(
            'username' =>  $username,
            'email' => $email,
            'fullname' => $fullname,
            'phone' => $phone,
            'salt' =>  $aes256Key,
            'photo' =>  $photo,
            'role' =>  $role,
            'tin' =>  $tin,
            'location' =>  $location,
            'designation' =>  $designation,
            'region' =>  $region,
            'district' =>  $district,
            'user_type' =>  $user_type,
            'baseurl' =>  $baseurl,
            'organization' =>  $organization,
            'access_level' =>  $access_level,
            'password' => $encryptedpassword,
            );

        try{
            if($this->user->update($data, $id)){
                if($this->db->find_object($id,"users_page","user_id")){
                    $this->db->updateByID("users_page", "user_id",$id, array("pages"=>$pages));
                }else{
                    $this->db->insert("users_page", array("user_id"=>$id, "pages"=>$pages));
                }
                $response['success'] = true;
                $response['message'] = "Account has been updated";
                return $response;
            }else {
                $this->http->_500(Msg::$account_update_error);
            }
        }catch(Exception $ex){
            $this->http->_500($ex->getMessage());
        }

    }
    
    public function update_profile()
    {
        $id = $this->http->json->id;
        $photo = trim($this->http->json->photo??null);
        $username = trim($this->http->json->username??null);
        if($username===null){
            $this->http->_403(Msg::$provide_username);
        }

        $fullname = $this->http->json->fullname??null;
        if($fullname===null){
            $this->http->_403(Msg::$provide_first_name);
        }

        $phone = trim($this->http->json->phone??null);
        $email = trim($this->http->json->email??null);


        //check if user with following information already exits in the system
        $this->db->query("SELECT * FROM `users` WHERE `username` = '$username' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::username_exist($username));
        }
        $this->db->query("SELECT * FROM `users` WHERE `email` = '$email' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::email_exist($email));
        }
        $this->db->query("SELECT * FROM `users` WHERE `phone` = '$phone' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::phone_exist($phone));
        }

        $data = array(
            'username' =>  $username,
            'email' => $email,
            'fullname' => $fullname,
            'phone' => $phone,
            'photo' =>  $photo,
            );

        try{
            if($this->user->update($data, $id)){
                $response['success'] = true;
                $response['message'] = "Account has been updated";
                return $response;
            }else {
                $this->http->_500(Msg::$account_update_error);
            }
        }catch(Exception $ex){
            $this->http->_500($ex->getMessage());
        }

    }
    
    public function changepass()
    {
        $id = $this->http->json->id;
        $email = $this->http->json->email;
        $cpassword = $this->http->json->cpassword??null;
        if(!$cpassword){
            $this->http->_403(Msg::$provide_current_password);
        }

        $password = $this->http->json->password??null;
        if(!$password){
            $this->http->_403(Msg::$provide_password);
        }

        if(strlen($password)<4){
            $this->http->_403(Msg::$incorrect_password_length);
        }

        $done = $this->user->login($email,$cpassword,null);
        if ($done) {
            $salt = Hash::salt($email);
            $aes256Key = Hash::make($salt,32);//store this key to db as salt
            $encryptedpassword = Hash::encrypt($password,$aes256Key);

            $data = array(
                'salt' =>  $aes256Key,
                'password' => $encryptedpassword,
                );

            try{
                if($this->user->update($data, $id)){
                    $response['success'] = true;
                    $response['message'] = "Account Password has been updated";
                    return $response;
                }else {
                    $this->http->_500(Msg::$account_update_error);
                }
            }catch(Exception $ex){
                $this->http->_500($ex->getMessage());
            }
        }else{
            $this->http->_500(Msg::$incorrect_password);
        }

    }

    public function delete(){
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $this->db->query("SELECT * FROM `users` WHERE `id` IN ($id)");
        $users = $this->db->results();
        $count = $this->db->count;
        $done = $this->db->query("DELETE FROM `users` WHERE `id` IN ($id)");
        if ($done) {
            $done = $this->db->query("DELETE FROM `users_page` WHERE `user_id` IN ($id)");
            if($users && $count >0){
                foreach ($users as $key => $value) {
                    $this->files->delete_file($value->photo);
                }
            }
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }

    public function upload(){
        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            if (isset($this->http->post->oldimage) && !empty($this->http->post->oldimage)) {
                $this->files->delete_file($this->http->post->oldimage);
            }
            $data = array(
                'photo' => $response['location'],
            );
            if (isset($this->http->post->id) && !empty($this->http->post->id)) {
                try {
                    if ($this->db->updateByID("users", "id", $this->http->post->id, $data)) {
                        $response['success'] = true;
                        $response['message'] = "Successfully uploaded user image";
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Error Uploading user image";
                        $this->files->delete_file($response['relative']);
                    }
                } catch (Exception $ex) {
                    $response['success'] = false;
                    $response['message'] = "Error (" . $ex->getMessage() . ")";
                    $this->files->delete_file($response['relative']);
                }
            } else {
                $response['success'] = true;
                $response['message'] = "Successfully uploaded user image";
            }
        }
        return $response;
    }

    public function delete_file(){
        $response = array();
        if (isset($this->http->json->oldimage) && !empty($this->http->json->oldimage)) {
            $this->files->delete_file($this->http->json->oldimage);
            $data = array(
                'photo' => NULL,
            );
            if (isset($this->http->json->id) && !empty($this->http->json->id)) {
                try {
                    if ($this->db->updateByID("users", "id", $this->http->json->id, $data)) {
                        $response['success'] = true;
                        $response['message'] = "Image removed succefully";
                    } else {
                        $response['success'] = false;
                        $response['message'] = "could not remove user image";
                    }
                } catch (Exception $ex) {
                    $response['success'] = false;
                    $response['message'] = "Error (" . $ex->getMessage() . ")";
                }
            } else {
                $response['success'] = true;
                $response['message'] = "Image removed succefully";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Error removing image";
        }
        return $response;
    }
}
