<?php

namespace Main\Models;

use Main\Domain\Car;

class CarModel extends DataModel {

  /**
   * Get car by calling parent class get method. 
   * 
   * @param { $registration = licens plate for the car to get.}
   * 
   * @return  { New Car object.}
   */
  public function get($registration) {
    $result = parent::getGeneric([
      "table" => "cars",
      "column" => "registration",
      "value" => $registration]);

    return new Car($result);
  }

  /**
   * Get all cars by calling parent class getAll method. 
   * 
   * @return  { Array of new Car objects.}
   */
  public function getAll() {
    $results = parent::getAllGeneric([
      "table" => "cars",
      "order" => "make"
    ]);

    foreach($results as $result) {
      $cars[] = new Car($result);
    }

    return $cars;
  }
  
  /**
   * Get all cars that are currently not rented by calling parent class
   * executeQuery method. 
   * 
   * @return  { Array of new Car objects.}
   */
  public function getAllNotRented() {
    $results = parent::executeQuery('SELECT * FROM cars WHERE checkedoutby IS NULL ORDER BY make');

    foreach($results as $result) {
      $cars[] = new Car($result);
    }

    return $cars;
  }
  
  /**
   * Get all cars that are currently rentedby calling parent class
   * executeQuery method. 
   * 
   * @return  { Array of new Car objects.}
   */
  public function getAllRented() {
    $results = parent::executeQuery('SELECT * FROM cars WHERE checkedoutby IS NOT NULL ORDER BY make');

    foreach($results as $result) {
      $cars[] = new Car($result);
    }

    return $cars;
  }

  /**
   * Insert (create) new car in the cars table by calling the parent class
   * create method. 
   * 
   * @param { $car = car object to create new row from.}
   * 
   * @return  { Car object created from the inserted data.}
   */
  public function create($car) {
    $query = <<<SQL
INSERT INTO cars(registration, make, color, year, price, checkedoutby, checkedouttime)
VALUES(:registration, :make, :color, :year, :price, :checkedoutby, :checkedouttime)
SQL;

    $specs = $car->toArray();
    
    parent::insertOrUpdateGeneric($query, $specs);

    return $this->get($car->getRegistration());
  }

  /**
   * Update (modify) car in the cars table by calling the parent class
   * edit method. 
   * 
   * @param { $car = car object to base the update on.}
   * 
   * @return  { Car object created from the updated data.}
   */
  public function edit($car) {
    $query = <<<SQL
UPDATE cars
SET make=:make, color=:color, year=:year, price=:price, checkedoutby=:checkedoutby, checkedouttime=:checkedouttime
WHERE registration=:registration
SQL;

    $specs = $car->toArray();
        
    parent::insertOrUpdateGeneric($query, $specs);

    return $this->get($car->getRegistration());
  }

  /**
   * Delete car by calling parent class delete method. 
   * 
   * @param { $registration = licens plate for the car to delete.}
   */
  public function delete($registration) {
    parent::deleteGeneric([
      "table" => "cars",
      "column" => "registration",
      "value" => $registration]);
  }

  /**
   * Get all possible car makes from the makes table. 
   */
  public function getMakes() {
    $makes = parent::executeQuery('SELECT * FROM makes');
 
    return $this->flatten($makes);
  }

  /**
   * Get all possible car colors from the colors table. 
   */
  public function getColors() {
    $colors = parent::executeQuery('SELECT * FROM colors');
 
    return $this->flatten($colors);
  }

  /**
   * Helper function to flatten the array of results fetched from 
   * the database, also removing duplicate indexed values.
   * 
   * @param {$arr = array to be flattened.}
   * 
   * @return  { The new array free from duplication.}
   */
  private function flatten($arr) {
    $num = count($arr);
    $newArr = [];

    for($count = 0; $count < $num; $count++) {
      $newArr[$count] =  $arr[$count][0];
    }

    return $newArr;
  }
}