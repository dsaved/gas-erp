<?php
class Userauth extends BaseController{
    public function __construct() {
    }

    public function index(){
      $this->model->index();
    }

    public function login(){
      $this->model->login();
    }
}