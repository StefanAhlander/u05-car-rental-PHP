<?php

namespace Main\Controllers;

use Main\Domain\Rental;
use Main\Models\RentalModel;
use Main\Models\CarModel;
use Main\Models\CustomerModel;
use Main\Core\FilteredMap;

class RentalController extends AbstractController {
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