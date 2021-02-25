<?php
class Liftings extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $liftings = $this->model->index();
      $this->http->reply(
          $liftings
      )->json();
    }
    
    public function import(){
      $liftings = $this->model->import();
      $this->http->reply(
          $liftings
      )->json();
    }

}