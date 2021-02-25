<?php
class Omcfallout extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $taxsTypes = $this->model->index();
      $this->http->reply(
          $taxsTypes
      )->json();
    }

    public function options(){
      $options = $this->model->options();
      $this->http->reply(
          $options
      )->json();
    }

    public function get(){
      $taxsType = $this->model->get();
      $this->http->reply(
          $taxsType
      )->json();
    }

    public function receipts(){
      $receipts = $this->model->receipts();
      $this->http->reply(
          $receipts
      )->json();
    }

    public function receipt_omc(){
      $receipts = $this->model->receipt_omc();
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