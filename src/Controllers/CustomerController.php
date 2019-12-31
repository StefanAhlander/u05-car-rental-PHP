<?php

namespace Main\Controllers;

use Main\Exceptions\NotFoundException;
use Main\Models\CustomerModel;

class CustomerController extends AbstractController {
    public function startPage() {
        $properties = ['errorMessage' => 'Page not found!'];
        return $this->render('main.twig', $properties);
    }
}