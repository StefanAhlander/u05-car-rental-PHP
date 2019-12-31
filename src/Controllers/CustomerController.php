<?php

namespace Main\Controllers;

use Main\Exceptions\NotFoundException;
use Main\Models\CustomerModel;
use Main\Core\FilteredMap;


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

  public function add() {
    $properties = [];
    return $this->render('addcustomer.twig', $properties);
  }

  public function addedCustomer() {
    $customerModel = new CustomerModel($this->db);

    $fM =  new FilteredMap($this->request->getForm());
    $newCustomer = [];
    $newCustomer["personnumber"] = $fM->getInt("personnumber");
    $newCustomer["name"] = $fM->getString("name");
    $newCustomer["address"] = $fM->getString("address");
    $newCustomer["postaladdress"] = $fM->getString("postaladdress");
    $newCustomer["phonenumber"] = $fM->getInt("phonenumber");

    try {
      $customerModel->createCustomer($newCustomer);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $newCustomer];
    return $this->render('addedcustomer.twig', $properties);
  }
}
