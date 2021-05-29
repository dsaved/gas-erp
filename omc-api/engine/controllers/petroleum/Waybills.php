<?php
class Waybills extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $result = $this->model->index();
      $this->http->reply(
          $result
      )->json();
    }

    public function reconcile(){
      $result = $this->model->reconcile();
      $this->http->reply(
          $result
      )->json();
    }

    public function analytics(){
      $result = $this->model->analytics();
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
  
    public function expected_declaration(){
      $result = $this->model->expected_declaration();
      $this->http->reply(
          $result
      )->json();
    }
}