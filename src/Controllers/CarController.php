<?php

namespace Main\Controllers;

use Main\Domain\Car;
use Main\Models\CarModel;
use Main\Models\RentalModel;
use Main\Core\FilteredMap;

class CarController extends AbstractController {

  public function getAll() {
    $carModel = new CarModel($this->db);

    try {
      $cars = $carModel->getAll();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting cars from Controller.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ['cars' => $cars];
    return $this->render('cars.twig', $properties);
  }

  public function editCar($registration) {
    $carModel = new CarModel($this->db);

    try {
      $makes = $carModel->getMakes();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting makes.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $colors = $carModel->getColors();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting colors.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $car = $carModel->get($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $car, "makes" => $makes, "colors" => $colors];
    return $this->render('editcar.twig', $properties);
  }




  public function add() {
    $carModel = new CarModel($this->db);

    try {
      $makes = $carModel->getMakes();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting makes.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $colors = $carModel->getColors();
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting colors.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["makes" => $makes, "colors" => $colors];
    return $this->render('addcar.twig', $properties);
  }


  
  public function editedCar() {
    $carModel = new CarModel($this->db);
    $car = $this->getCarFromForm();

    try {
      $editedCar = $carModel->edit($car);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error editing car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $editedCar];
    return $this->render('editedcar.twig', $properties);
  }




  public function addedCar() {
    $carModel = new CarModel($this->db);
    $newCar = $this->getCarFromForm();

    try {
      $addedCar = $carModel->create($newCar);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error adding car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $addedCar];
    return $this->render('addedcar.twig', $properties);
  }





  public function deleteCar($registration) {
    $carModel = new CarModel($this->db);
    $rentalModel = new RentalModel($this->db);

    try {
      $car = $carModel->get($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Car not found.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $rentalModel->removeCar($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error removing car from rental history.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $carModel->delete($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error deleteing car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $car];
    return $this->render('deletedcar.twig', $properties);
  }

  /**
   * Helper function to get form data.
   */
  private function getCarFromForm() {
    $fM =  new FilteredMap($this->request->getForm());
    $car["registration"] = $fM->getString("registration");
    $car["make"] = $fM->getString("make");
    $car["color"] = $fM->getString("color");
    $car["year"] = $fM->getInt("year");
    $car["price"] = $fM->getInt("price");

    return new Car($car);
  }
}