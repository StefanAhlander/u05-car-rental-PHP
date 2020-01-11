<?php

namespace Main\Controllers;

use Main\Domain\Car;
use Main\Models\CarModel;
use Main\Models\RentalModel;
use Main\Core\FilteredMap;

/**
 * Handles almost all car related functionality as reguested by the provided URI. 
 */
class CarController extends ParentController {

  /**
   * Instantiate the CarModel object and call it's getAll function. 
   * If there is an error calling the CarModel method populate the
   * 'properies' variable with an error message and render the 
   * error view using Twig. 
   * Call a method to get all currently rented cars. Loop over the results
   * and combine these with the prvious results to create the complete 
   * car objects to pass to the render method of Twig. 
   */
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

  /** 
   * Get makes and colors from their respective tables in the datase. 
   * Use these arrays to populate the 'add car' view.
   */
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

  /** 
   * Get form data from the 'add car' view form element and call the CarModel with this
   * information to create a new row in the database representing this new
   * car. Render view to show the car that was added.
   */
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

  /** 
   * Get makes and colors from their respective tables in the datase. 
   * Also call the CarModel method to get stored information about the
   * car to be edited. Use these arrays to populate the 'edit car' view.
   * 
   * @param { $registration = the licens plate for the car passed in the URI.}
   */
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

  /** 
   * Get form data from the 'edit car' view form element and call the CarModel with this
   * information to create a new row in the database representing this new
   * car. Render view to show the car that was edited.
   */
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

  /** 
   * Call the CarModel to get all information about the car to be deleted. 
   * Also call the RentalModel to remove all occurances of the car from the rentals table.
   * Finally call the CarModel to delete the car from the database. Use the previously
   * gotten information about the car to populate a view showing what car has been 
   * deleted. Using this information offers the possibility to undo the delete action 
   * by calling the 'added car' function by navigation that route from the 'deleted view'.
   * Unfortunately the removal of all occurances from the rentals table can not be
   * rolled back the same way. 
   * 
   * @param { $registration = the licens plate for the car passed in the URI.} 
   */
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
   * Helper function to get form data. Also instantiates a new car object based
   * on this data.
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