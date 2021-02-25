<?php
class Options extends SecureController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
    }

    //banks options
    public function banks()
    {
        $options = $this->model->banks_options();
        $this->http->reply(
            $options
        )->json();
    }

}
