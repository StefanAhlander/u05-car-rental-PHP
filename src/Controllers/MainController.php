<?php

namespace Main\Controllers;

class MainController extends AbstractController {

  public function startPage() {
    $properties = [];
    return $this->render('main.twig', $properties);
  }
}