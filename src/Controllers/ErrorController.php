<?php

namespace Main\Controllers;

class ErrorController extends AbstractController {
    public function notFound() {
        $properties = ['errorMessage' => 'Page not found!'];
        return $this->render('error.twig', $properties);
    }
}