<?php

namespace Main\Controllers;

use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use Main\Models\CarModel;
use Main\Core\FilteredMap;

class CarController extends AbstractController {

  public function getAll() {
    $carModel = new CarModel($this->db);

    $cars = $carModel->getAll();

    $properties = [
      'cars' => $cars
    ];
    return $this->render('cars.twig', $properties);
  }

  public function editCar($registration) {
    $carModel = new CarModel($this->db);
    $makes = $carModel->getMakes();
    $colors = $carModel->getColors();

    try {
      $car = $carModel->get($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error getting car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $car, "makes" => $makes, "colors" => $colors];
    return $this->render('editcar.twig', $properties);
  }

  public function editedCar() {
    $carModel = new CarModel($this->db);
    $car = $this->getCarFromForm();

    try {
      $editedCar = $carModel->editCar($car);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error editing car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $editedCar];
    return $this->render('editedcar.twig', $properties);
  }



  public function add() {
    $carModel = new CarModel($this->db);
    $makes = $carModel->getMakes();
    $colors = $carModel->getColors();

    $properties = ["makes" => $makes, "colors" => $colors];
    return $this->render('addcar.twig', $properties);
  }


  public function addedCar() {
    $carModel = new CarModel($this->db);
    $newCar = $this->getCarFromForm();

    try {
      $addedCar = $carModel->addCar($newCar);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error adding car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $addedCar];
    return $this->render('addedcar.twig', $properties);
  }

  public function deleteCar($registration) {
    $carModel = new CarModel($this->db);

    try {
      $car = $carModel->get($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Car not found.'];
      return $this->render('error.twig', $properties);
    }

    try {
      $carModel->deleteCar($registration);
    } catch (\Exception $e) {
      $properties = ['errorMessage' => 'Error deleteing car.'];
      return $this->render('error.twig', $properties);
    }

    $properties = ["car" => $car];
    return $this->render('deletedcar.twig', $properties);
  }




  private function getCarFromForm() {
    $fM =  new FilteredMap($this->request->getForm());
    $car["registration"] = $fM->getString("registration");
    $car["make"] = $fM->getString("make");
    $car["color"] = $fM->getString("color");
    $car["year"] = $fM->getInt("year");
    $car["price"] = $fM->getInt("price");

    return $car;
  }








    public function borrow(int $bookId): string {
        $bookModel = new BookModel($this->db);

        try {
            $book = $bookModel->get($bookId);
        } catch (NotFoundException $e) {
            $this->log->warn('Book not found: ' . $bookId);
            $params = ['errorMessage' => 'Book not found.'];
            return $this->render('error.twig', $params);
        }

        if (!$book->getCopy()) {
            $params = ['errorMessage' => 'There are no copies left.'];
            return $this->render('error.twig', $params);
        }

        try {
            $bookModel->borrow($book, $this->customerId);
        } catch (DbException $e) {
            $this->log->warn('Error borrowing book: ' . $e->getMessage());
            $params = ['errorMessage' => 'Error borrowing book.'];
            return $this->render('error.twig', $params);
        }

        return $this->getByUser();
    }

    public function returnBook(int $bookId): string {
        $bookModel = new BookModel($this->db);

        try {
            $book = $bookModel->get($bookId);
        } catch (NotFoundException $e) {
            $this->log->warn('Book not found: ' . $bookId);
            $params = ['errorMessage' => 'Book not found.'];
            return $this->render('error.twig', $params);
        }

        $book->addCopy();

        try {
            $bookModel->returnBook($book, $this->customerId);
        } catch (DbException $e) {
            $this->log->warn('Error borrowing book: ' . $e->getMessage());
            $params = ['errorMessage' => 'Error borrowing book.'];
            return $this->render('error.twig', $params);
        }

        return $this->getByUser();
    }
}