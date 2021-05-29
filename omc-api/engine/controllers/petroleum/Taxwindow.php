<?php
class Taxwindow extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $taxTypes = $this->model->index();
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
}