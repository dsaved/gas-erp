<?php
class Banks extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $users = $this->model->index();
    $this->http->reply(
        $users
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
    $user = $this->model->get();
    $this->http->reply(
        $user
    )->json();
  }

  public function delete(){
    $users = $this->model->delete();
    $this->http->reply(
        $users
    )->json();
  }
}