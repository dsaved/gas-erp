<?php
class Receipts extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $receipts = $this->model->index();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function get(){
      $receipt = $this->model->get();
      $this->http->reply(
          $receipt
      )->json();
    }

    public function receipt_omc(){
      $receipt = $this->model->receipt_omc();
      $this->http->reply(
          $receipt
      )->json();
    }

    public function banks(){
      $receipt = $this->model->index();
      $this->http->reply(
          $receipt
      )->json();
    }

    public function remove(){
      $receipt = $this->model->remove();
      $this->http->reply(
          $receipt
      )->json();
    }
}