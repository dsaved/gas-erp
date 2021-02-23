<?php
class Http{
    public $post;
    public $get;
    public $files;
    public $json;
    public static $response_data;

    public function __construct() {
        $this->post = array_to_object($_POST);
        $this->get = array_to_object($_GET);
        $this->files = $_FILES;
        $this->json = json_decode(file_get_contents("php://input"));
    }

    public function reply($data){
        self::$response_data = $data;
        return $this;
    }

    public function plain(){
        if(is_array(self::$response_data) || is_object(self::$response_data)){
            print_r(self::$response_data);
        }else{
            print(self::$response_data);
        }
        exit();
    }

    public function json($exit = true){
        render_json(self::$response_data);
    }

    public function warn(){
        if(is_array(self::$response_data) || is_object(self::$response_data)){
            print_r(self::$response_data);
        }else{
            print(self::$response_data);
        }
    }
    
	function dump() {
        var_dump(self::$response_data);
        exit();
     }

	function _405($msg=null) {
        http_response_code(405);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Method Not Allowed",
            ])->json();
     }

	function _500($msg=null) {
        http_response_code(500);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Internal Server Error",
            ])->json();
     }
     
	function _403($msg=null) {
        http_response_code(403);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Forbidden request",
            ])->json();
     }
     
	function _400($msg=null) {
        http_response_code(400);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Bad request",
            ])->json();
     }
     
	function _401($msg=null) {
        http_response_code(401);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Authorization Required",
            ])->json();
     }

	function _404($msg=null) {
        http_response_code(404);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"Resource not found",
            ])->json();
     }

	function _204($msg=null) {
        http_response_code(204);
        $this->reply(
            [
                "success" => false,
                "message" => $msg??"No Content",
            ])->json();
     }
}