<?php
class Outlet extends SecureController {
    public function __construct() {
      parent::__construct();
    }

    public function outlets(){
      $result = $this->model->outlets();
      $this->http->reply(
          $result
      )->json();
    }
}