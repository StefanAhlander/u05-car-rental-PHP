<?php

namespace Main\Controllers;

use Main\Models\RentalModel;
use Main\Models\CarModel;
use Main\Models\CustomerModel;
use Main\Core\FilteredMap;

/**
 * Handles almost all rental related functionality as reguested by the provided URI. 
 */
class RentalController extends ParentController {

  /**
   * Instantiate both a CarModel object and a CustomerModel object.
   * Use these to get a list of all cars not rented and all customers. 
   * Use these lists to populate the 'check out' view with select input
   * elements.
   */
  public function checkOutCar() {
    $carModel = new CarModel($this->db);
    $customerModel = new CustomerModel($this->db);

    try {
      $cars = $carModel->getAllNotRented();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating cars not rented.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $customers = $customerModel->getAll();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating customers.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["cars" => $cars, "customers" => $customers];
    return $this->render('checkout.twig', $properties);
  }

  /**
   * Instantiate a new RentalModel object. Get person number of the customer
   * renting and the licens number of the car to be rented from the form on the
   * checkout page. Use this information to create a new row in the rentals
   * table in the database. Use the returned id to get the row back from the database
   * showing i.e. checkout time etc. Use this info to populate a view showing the
   * newly created rental.
   */
  public function checkedOutCar() {
    $rentalModel = new RentalModel($this->db);

    $fM =  new FilteredMap($this->request->getForm());
    $personnumber = $fM->getInt("personnumber");
    $registration = $fM->getString("registration");
    
    try {
      $id = $rentalModel->createRental($personnumber, $registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error creating rental.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $rental = $rentalModel->get($id);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting rental by id.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["rental" => $rental];
    return $this->render('checkedout.twig', $properties);
  }

  /**
   * Instantiate a CarModel object. Use a method of the object to get a list of 
   * all cars currently rented. Use the list to populate the checkin view.
   */
  public function checkInCar() {
    $carModel = new CarModel($this->db);

    try {
      $cars = $carModel->getAllRented();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting all rented cars.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["cars" => $cars];
    return $this->render('checkin.twig', $properties);
  }

  /**
   * Instantiate a new RentalModel object. Get the licens plate (registration) of the 
   * car to be checked in from the form element on the checkin view. Pass this 
   * registration to the object method to close the rental. Use the returned id to
   * get the corresponding row from the database table. Use this information to
   * populate a view showing the closed rental.
   */
  public function checkedInCar() {
    $rentalModel = new RentalModel($this->db);

    $fM =  new FilteredMap($this->request->getForm());
    $registration = $fM->getString("registration");
    
    try {
      $id = $rentalModel->closeRental($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error closing rental.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $rental = $rentalModel->get($id);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting rental by id.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["rental" => $rental];
    return $this->render('checkedin.twig', $properties);
  }

  /**
   * Get and show all rental history.
   */
  public function getHistory() {
    $rentalModel = new RentalModel($this->db);

    try {
      $rentals = $rentalModel->getAll();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting rental history.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["rentals" => $rentals];
    return $this->render('history.twig', $properties);
  }
}