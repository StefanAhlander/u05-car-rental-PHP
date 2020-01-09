<?php

namespace Main\Controllers;

use Main\Domain\Customer;
use Main\Models\CustomerModel;
use Main\Models\RentalModel;
use Main\Core\FilteredMap;

/**
 * Handles almost all customer related functionality as reguested by the provided URI. 
 */
class CustomerController extends ParentController {

  /**
   * Instantiate the CustomerModel object and call it's getAll function. 
   * If there is an error calling the CustomerModel method populate the
   * 'properies' variable with an error message and render the 
   * error view using Twig. If there is no error use the returned array
   * to populate the 'customers' view using Twig. 
   */
  public function getAll() {
    $customerModel = new CustomerModel($this->db);
    
    try {
      $customers = $customerModel->getAll();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting customers from Controller.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ['customers' => $customers];
    //die(var_dump($properties));

    return $this->render('customers.twig', $properties);
  }

  /**
   * Render the add customer view.
   */
  public function add() {
    $properties = [];
    return $this->render('addcustomer.twig', $properties);
  }

  /** 
   * Get form data from the 'add customer' view form element and call the 
   * CustomerModel with this information to create a new row in the database 
   * representing this new customer.Render view to show the customer that was added.
   */
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

  /** 
   * Get the person number of the customer to be edited from the hidden form element. 
   * This is not passed in the URI because of it's sensitive nature!!
   * 
   * Call the get method in the CustomerModel to get all customer information
   * from the database. Use this info to populate the view.
   */
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

  /** 
   * Get form data from the 'edit customer' view form element and call the CustomerModel 
   * with this information to create a new row in the database representing this new
   * customer. Render view to show the customer that was edited.
   */
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

  /** 
   * Get the person number of the customer to be deleted from the hidden form element. 
   * This is not passed in the URI because of it's sensitive nature!!
   * 
   * Call the CustomerModel to get all information about the customer to be deleted. 
   * Also call the RentalModel to remove all occurances of the customer from the rentals table.
   * Finally call the CustomerModel to delete the customer from the database. Use the previously
   * gotten information about the customer to populate a view showing what customer has been 
   * deleted. Using this information offers the possibility to undo the delete action 
   * by calling the 'added customer' function by navigation that route from the 'deleted view'.
   * Unfortunately the removal of all occurances from the rentals table can not be
   * rolled back the same way. 
   *  
   */
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
   * Helper function to get form data. Also instantiates a new customer object
   * based on this data.
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
