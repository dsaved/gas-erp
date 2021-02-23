<?php
class Statements extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $statements = $this->model->index();
    $this->http->reply(
        $statements
    )->json();
  }

  public function options(){
    $options = $this->model->options();
    $this->http->reply(
        $options
    )->json();
  }

  public function create(){
    $done = $this->model->create();
    $this->http->reply(
        $done
    )->json();
  }

  public function update(){
    $done = $this->model->update();
    $this->http->reply(
        $done
    )->json();
  }

  public function get(){
    $statements = $this->model->get();
    $this->http->reply(
        $statements
    )->json();
  }

  public function statement_account(){
    $statements = $this->model->statement_account();
    $this->http->reply(
        $statements
    )->json();
  }

  public function account_statement(){
    $statements = $this->model->account_statement();
    $this->http->reply(
        $statements
    )->json();
  }

  public function delete(){
    $statements = $this->model->delete();
    $this->http->reply(
        $statements
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
}