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
    
    public function start_export()
    {
        $options = $this->model->start_export();
        $this->http->reply(
            $options
        )->json();
    }

    public function file_export_status()
    {
        $options = $this->model->file_export_status();
        $this->http->reply(
            $options
        )->json();
    }
}
