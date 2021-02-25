<?php
class Omclog extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $omcs = $this->model->index();
      $this->http->reply(
          $omcs
      )->json();
    }

    public function transactions(){
      $transactions = $this->model->transactions();
      $this->http->reply(
          $transactions
      )->json();
    }

}