<?php
class Auditlogs extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $reslut = $this->model->index();
    $this->http->reply(
        $reslut
    )->json();
  }

  public function auditslogs_transactions(){
    $reslut = $this->model->auditslogs_transactions();
    $this->http->reply(
        $reslut
    )->json();
  }

  public function get(){
    $options = $this->model->get();
    $this->http->reply(
        $options
    )->json();
  }

  public function unreconcile(){
    $reslut = $this->model->unreconcile();
    $this->http->reply(
        $reslut
    )->json();
  }

  public function start_export(){
    $reslut = $this->model->start_export();
    $this->http->reply(
        $reslut
    )->json();
  }

  public function file_export_status(){
    $reslut = $this->model->file_export_status();
    $this->http->reply(
        $reslut
    )->json();
  }
}