<?php
defined('APP') or exit('No direct script access allowed');

class BaseModel
{
    public function __construct() {
        ini_set('memory_limit','1028M');
        $this->db = new DB();
        $this->sms = new SMS();
        $this->files = new Files();
        $this->user = new User();
        $this->date = new Date();
        $this->paging = new Pagination();
        $this->http = new Http();

        if (isset($_POST) && !empty($_POST)) {
            $this->param = array_to_object($_POST);
        } elseif (isset($_GET) && !empty($_GET)) {
            $this->param = array_to_object($_GET);
        }
    }
}