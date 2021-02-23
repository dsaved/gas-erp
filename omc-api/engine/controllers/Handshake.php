<?php
class Handshake extends BaseController {
    public function __construct() {
        parent::__construct();
    }

    public function index(){
        $handshake = $this->http->json->data;
        $handshake = base64_decode($handshake);
        $info = explode(":n:n", $handshake);
        $this->model->initHandshake($info);
    }
}