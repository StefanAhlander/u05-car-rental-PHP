<?php

namespace Main\Controllers;

/**
 * Renders the startpage of the app when no path is passed in the URI.
 * 
 * This is instead of the 'main menu view' that was part of the assignement. I have 
 * implemented the main view to be visible at all times instead for increased usability. 
 */
class MainController extends ParentController {

  public function startPage() {
    $properties = [];
    return $this->render('main.twig', $properties);
  }
}