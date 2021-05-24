<?php
class Analytics extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function manifest(){
      $results = $this->model->manifest();
      $this->http->reply(
          $results
      )->json();
    }
    
    public function orders(){
      $results = $this->model->orders();
      $this->http->reply(
          $results
      )->json();
    }

}