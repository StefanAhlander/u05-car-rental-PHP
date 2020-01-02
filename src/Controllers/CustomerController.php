<?php

namespace Main\Controllers;

use Main\Exceptions\NotFoundException;
use Main\Models\CustomerModel;
use Main\Core\FilteredMap;

class CustomerController extends AbstractController {
  const PAGE_LENGTH = 10;

  public function editCustomer() {
    $customerModel = new CustomerModel($this->db);
    $fM =  new FilteredMap($this->request->getForm());
    $personnumber = $fM->getInt("personnumber");

    try {
      $customer = $customerModel->getCustomer($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $customer];
    return $this->render('editcustomer.twig', $properties);
  }

  public function deleteCustomer() {
    $customerModel = new CustomerModel($this->db);
    $fM =  new FilteredMap($this->request->getForm());
    $personnumber = $fM->getInt("personnumber");

    try {
      $customer = $customerModel->getCustomer($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Customer not found.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $customerModel->deleteCustomer($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error deleteing customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $customer];
    return $this->render('deletedcustomer.twig', $properties);
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
    $newCustomer = $this->getCustomerFromForm();

    try {
      $addedCustomer = $customerModel->createCustomer($newCustomer);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $addedCustomer];
    return $this->render('addedcustomer.twig', $properties);
  }

  public function editedCustomer() {
    $customerModel = new CustomerModel($this->db);
    $newCustomer = $this->getCustomerFromForm();

    try {
      $editedCustomer = $customerModel->editCustomer($newCustomer);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error editing customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $editedCustomer];
    return $this->render('editedcustomer.twig', $properties);
  }

  private function getCustomerFromForm() {
    $fM =  new FilteredMap($this->request->getForm());
    $customer["personnumber"] = $fM->getInt("personnumber");
    $customer["name"] = $fM->getString("name");
    $customer["address"] = $fM->getString("address");
    $customer["postaladdress"] = $fM->getString("postaladdress");
    $customer["phonenumber"] = $fM->getString("phonenumber");

    return $customer;
  }
}
