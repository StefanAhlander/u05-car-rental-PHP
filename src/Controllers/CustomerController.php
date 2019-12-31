<?php

namespace Main\Controllers;

use Main\Exceptions\NotFoundException;
use Main\Models\CustomerModel;

class CustomerController extends AbstractController {
    const PAGE_LENGTH = 10;

    public function startPage() {
        $properties = [];
        return $this->render('main.twig', $properties);
    }

    public function getAllWithPage($page) {
        $page = (int)$page;

        $customerModel = new CustomerModel($this->db);

        $customers = $customerModel->getAll($page, self::PAGE_LENGTH);

        $properties = [
            'customers' => $customers,
            'currentPage' => $page,
            'lastPage' => count($customers) < self::PAGE_LENGTH
        ];
        return $this->render('customers.twig', $properties);
    }

    public function getAll() {
        return $this->getAllWithPage(1);
    }
}