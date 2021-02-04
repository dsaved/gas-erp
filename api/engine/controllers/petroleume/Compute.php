<?php
class Compute extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $this->compute();
    }

    public function compute(){
      $compute = $this->model->compute();
      $this->http->reply(
          $compute
      )->json();
    }

}