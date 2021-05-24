<?php
class Tanks extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $result = $this->model->index();
      $this->http->reply(
          $result
      )->json();
    }

    public function depot(){
      $result = $this->model->depotsTank();
      $this->http->reply(
          $result
      )->json();
    }

    public function invalid_pump(){
      $result = $this->model->invalid_pump();
      $this->http->reply(
          $result
      )->json();
    }
}