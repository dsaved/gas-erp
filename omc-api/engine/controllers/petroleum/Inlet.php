<?php
class Inlet extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function inlets(){
      $result = $this->model->inlets();
      $this->http->reply(
          $result
      )->json();
    }
}