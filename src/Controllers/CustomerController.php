<?php

namespace Main\Controllers;

use Main\Domain\Customer;
use Main\Models\CustomerModel;
use Main\Models\RentalModel;
use Main\Core\FilteredMap;

class CustomerController extends AbstractController {

  public function getAll() {
    $customerModel = new CustomerModel($this->db);
    $customers = $customerModel->getAll();

    $properties = ['customers' => $customers];
    //die(var_dump($properties));

    return $this->render('customers.twig', $properties);
  }

  public function add() {
    $properties = [];
    return $this->render('addcustomer.twig', $properties);
  }

  public function addedCustomer() {
    $customerModel = new CustomerModel($this->db);
    $newCustomer = $this->getCustomerFromForm();

    try {
      $addedCustomer = $customerModel->create($newCustomer);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $addedCustomer];
    return $this->render('addedcustomer.twig', $properties);
  }

  public function editCustomer() {
    $customerModel = new CustomerModel($this->db);
    $fM =  new FilteredMap($this->request->getForm());
    $personnumber = $fM->getInt("personnumber");

    try {
      $customer = $customerModel->get($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $customer];
    return $this->render('editcustomer.twig', $properties);
  }



  public function editedCustomer() {
    $customerModel = new CustomerModel($this->db);
    $newCustomer = $this->getCustomerFromForm();

    try {
      $editedCustomer = $customerModel->edit($newCustomer);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error editing customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $editedCustomer];
    return $this->render('editedcustomer.twig', $properties);
  }





  public function deleteCustomer() {
    $customerModel = new CustomerModel($this->db);
    $rentalModel = new RentalModel($this->db);
    $fM =  new FilteredMap($this->request->getForm());
    $personnumber = $fM->getInt("personnumber");

    try {
      $customer = $customerModel->get($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Customer not found.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $rentalModel->removeCustomer($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error removing customer from rental history.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $customerModel->delete($personnumber);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error deleteing customer.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["customer" => $customer];
    return $this->render('deletedcustomer.twig', $properties);
  }

  /**
   * Helper function to get form data.
   */
  private function getCustomerFromForm() {
    $fM =  new FilteredMap($this->request->getForm());
    $customer["personnumber"] = $fM->getInt("personnumber");
    $customer["name"] = $fM->getString("name");
    $customer["address"] = $fM->getString("address");
    $customer["postaladdress"] = $fM->getString("postaladdress");
    $customer["phonenumber"] = $fM->getString("phonenumber");

    return new Customer($customer);
  }
}
