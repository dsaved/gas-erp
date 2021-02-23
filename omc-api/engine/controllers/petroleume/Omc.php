<?php
class Omc extends SecureController {
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

    public function import(){
      $done = $this->model->import();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function import_status(){
      $status = $this->model->import_status();
      $this->http->reply(
          $status
      )->json();
    }

    public function accounts(){
      $done = $this->model->accounts();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function start_reconcilation(){
      $done = $this->model->start_reconcilation();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function reconcilation_status(){
      $done = $this->model->reconcilation_status();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function map(){
      $done = $this->model->map();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function unmap(){
      $done = $this->model->unmap();
      $this->http->reply(
          $done
      )->json();
    }
  
    public function empty_receipts(){
      $done = $this->model->empty_receipts();
      $this->http->reply(
          $done
      )->json();
    }
}