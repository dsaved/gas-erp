<?php
class Accountcategory extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $cats = $this->model->index();
    $this->http->reply(
        $cats
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
    $cats = $this->model->delete();
    $this->http->reply(
        $cats
    )->json();
  }
}