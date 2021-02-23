<?php
class Bankaccounts extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $users = $this->model->index();
    $this->http->reply(
        $users
    )->json();
  }

  public function options_otherbanks(){
    $options = $this->model->options_otherbanks();
    $this->http->reply(
        $options
    )->json();
  }

  public function options_category(){
    $options = $this->model->options_category();
    $this->http->reply(
        $options
    )->json();
  }

  public function options_owners(){
    $options = $this->model->options_owners();
    $this->http->reply(
        $options
    )->json();
  }

  public function bogbank(){
    $bogbank = $this->model->bogbank();
    $this->http->reply(
        $bogbank
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

  public function editbank(){
    $editbank = $this->model->editbank();
    $this->http->reply(
        $editbank
    )->json();
  }

  public function delete(){
    $users = $this->model->delete();
    $this->http->reply(
        $users
    )->json();
  }

  public function empty_statement(){
    $done = $this->model->empty_statement();
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

  public function reconcile(){
    $done = $this->model->reconcile();
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

  public function add_to_sub_account(){
    $done = $this->model->add_to_sub_account();
    $this->http->reply(
        $done
    )->json();
  }

  public function remove_sub_account(){
    $done = $this->model->remove_sub_account();
    $this->http->reply(
        $done
    )->json();
  }
}