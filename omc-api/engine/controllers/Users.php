<?php
class Users extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function index(){
      $users = $this->model->index();
      $this->http->reply(
          $users
      )->json();
    }

    public function omc(){
      $users = $this->model->omc();
      $this->http->reply(
          $users
      )->json();
    }

    public function bdc(){
      $users = $this->model->bdc();
      $this->http->reply(
          $users
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

    public function update_profile(){
      $done = $this->model->update_profile();
      $this->http->reply(
          $done
      )->json();
    }

    public function changepass(){
      $done = $this->model->changepass();
      $this->http->reply(
          $done
      )->json();
    }

    public function update_password(){
      $done = $this->model->update_password();
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

    public function profile(){
      $user = $this->model->profile();
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

    public function upload(){
      $done = $this->model->upload();
      $this->http->reply(
          $done
      )->json();
    }

    public function delete_file(){
      $done = $this->model->delete_file();
      $this->http->reply(
          $done
      )->json();
    }

    public function get_role(){
      $done = $this->model->get_role();
      $this->http->reply(
          $done
      )->json();
    }

    public function get_roles(){
      $done = $this->model->get_roles();
      $this->http->reply(
          $done
      )->json();
    }

    public function role_add(){
      $done = $this->model->role_add();
      $this->http->reply(
          $done
      )->json();
    }

    public function role_update(){
      $done = $this->model->role_update();
      $this->http->reply(
          $done
      )->json();
    }

    public function role_delete(){
      $done = $this->model->role_delete();
      $this->http->reply(
          $done
      )->json();
    }
}