<?php
class Nationalsummary extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $receipts = $this->model->reconciled();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function unreconciled(){
      $receipts = $this->model->unreconciled();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function getsingle_reconciled(){
      $receipts = $this->model->getsingle_reconciled();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function getsingle_unreconciled(){
      $receipts = $this->model->getsingle_unreconciled();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function start_export(){
      $receipts = $this->model->start_export();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function file_export_status(){
      $receipts = $this->model->file_export_status();
      $this->http->reply(
          $receipts
      )->json();
    }

}