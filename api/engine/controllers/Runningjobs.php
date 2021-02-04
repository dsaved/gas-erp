<?php
class Runningjobs extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $jobs = $this->model->index();
    $this->http->reply(
        $jobs
    )->json();
  }

  public function files(){
    $files = $this->model->files();
    $this->http->reply(
        $files
    )->json();
  }

  public function delete(){
    $delete = $this->model->delete();
    $this->http->reply(
        $delete
    )->json();
  }
}