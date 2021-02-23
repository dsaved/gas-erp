<?php
class Home extends SecureController {
    public function __construct() {
        parent::__construct();
    }

    public function index(){
        $done = $this->model->statistics();
        $this->http->reply(
            $done
        )->json();
    }

    public function load_balance(){
        $done = $this->model->load_balance();
        $this->http->reply(
            $done
        )->json();
    }
}