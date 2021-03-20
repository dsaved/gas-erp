<?php
class Surcharge extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $taxTypes = $this->model->index();
      $this->http->reply(
          $taxTypes
      )->json();
    }

    public function accounts(){
      $taxTypes = $this->model->accounts();
      $this->http->reply(
          $taxTypes
      )->json();
    }

    public function charges(){
      $taxTypes = $this->model->charges();
      $this->http->reply(
          $taxTypes
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

    public function add(){
      $done = $this->model->addtax();
      $this->http->reply(
          $done
      )->json();
    }

    public function update(){
      $done = $this->model->updatetax();
      $this->http->reply(
          $done
      )->json();
    }
    
    public function delete(){
      $done = $this->model->deletetax();
      $this->http->reply(
          $done
      )->json();
    }
    
    public function compile(){
      $done = $this->model->compile();
      $this->http->reply(
          $done
      )->json();
    }
    
    public function compilation_status(){
      $done = $this->model->compilation_status();
      $this->http->reply(
          $done
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