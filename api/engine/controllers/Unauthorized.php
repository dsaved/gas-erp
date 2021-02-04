<?php
class Unauthorized extends SecureController {
  public function __construct() {
    parent::__construct();
  }

  public function index(){
    $ut = $this->model->index();
    $this->http->reply(
        $ut
    )->json();
  }

  public function recipients(){
    $recipients = $this->model->recipients();
    $this->http->reply(
        $recipients
    )->json();
  }

  public function transactions(){
    $transactions = $this->model->transactions();
    $this->http->reply(
        $transactions
    )->json();
  }

  public function reciver_transactions(){
    $transactions = $this->model->reciver_transactions();
    $this->http->reply(
        $transactions
    )->json();
  }

  public function response_transactions(){
    $transactions = $this->model->response_transactions();
    $this->http->reply(
        $transactions
    )->json();
  }

  public function get(){
    $transactions = $this->model->get();
    $this->http->reply(
        $transactions
    )->json();
  }

  public function submitresponse(){
    $done = $this->model->submitresponse();
    $this->http->reply(
        $done
    )->json();
  }

  public function updateresponse(){
    $done = $this->model->updateresponse();
    $this->http->reply(
        $done
    )->json();
  }

  public function upload_file(){
    $done = $this->model->upload_file();
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

  public function remove_reconcilation(){
    $done = $this->model->remove_reconcilation();
    $this->http->reply(
        $done
    )->json();
  }

  public function send_to_bog(){
    $done = $this->model->send_to_bog();
    $this->http->reply(
        $done
    )->json();
  }

  public function send_all_to_bog(){
    $done = $this->model->send_all_to_bog();
    $this->http->reply(
        $done
    )->json();
  }

  public function start_export(){
    $done = $this->model->start_export();
    $this->http->reply(
        $done
    )->json();
  }

  public function file_export_status(){
    $done = $this->model->file_export_status();
    $this->http->reply(
        $done
    )->json();
  }

  public function hide(){
    $done = $this->model->hide();
    $this->http->reply(
        $done
    )->json();
  }

  public function unhide(){
    $done = $this->model->unhide();
    $this->http->reply(
        $done
    )->json();
  }

  public function group_hide(){
    $done = $this->model->group_hide();
    $this->http->reply(
        $done
    )->json();
  }

  public function group_unhide(){
    $done = $this->model->group_unhide();
    $this->http->reply(
        $done
    )->json();
  }

}