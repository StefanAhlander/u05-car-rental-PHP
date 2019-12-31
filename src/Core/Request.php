<?php
  namespace Main\Core;

  class Request {
    private $path, $form;

    public function __construct() {
      $this->path = $_SERVER["REQUEST_URI"];
      $this->form = array_merge($_POST, $_GET);
    }

    public function getPath() {
      return $this->path;
    }

    public function getForm() {
      return $this->form;
    }
  }
?>