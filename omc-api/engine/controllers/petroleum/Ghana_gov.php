<?php
class Ghana_gov extends SecureController {
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
  
    public function difference(){
      $result = $this->model->difference();
      $this->http->reply(
          $result
      )->json();
    }
  
    public function options_list(){
      $result = $this->model->options();
      $this->http->reply(
          $result
      )->json();
    }
}