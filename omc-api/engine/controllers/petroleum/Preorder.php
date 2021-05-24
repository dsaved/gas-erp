<?php
class Preorder extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $result = $this->model->index();
      $this->http->reply(
          $result
      )->json();
    }

    public function import(){
      $done = $this->model->import();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function import_status(){
      $result = $this->model->import_status();
      $this->http->reply(
          $result
      )->json();
    }
  
    public function delete(){
      $result = $this->model->delete();
      $this->http->reply(
          $result
      )->json();
    }
}