<?php

namespace Main\Controllers;

/**
 * Renders an error view for page not found.
 */
class ErrorController extends ParentController {
  public function notFound() {
    $properties = ['errorMessage' => 'Page not found!'];
    return $this->render('error.twig', $properties);
  }
}