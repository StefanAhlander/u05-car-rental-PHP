<?php

namespace Main\Models;

use Main\Domain\Book;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;

class CarModel extends AbstractModel {
  const CLASSNAME = '\Main\Domain\Car';

  public function get($registration) {
    $query = 'SELECT * FROM cars WHERE registration = :registration';
    $sth = $this->db->prepare($query);
    $sth->execute(['registration' => $registration]);

    $cars = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    if (empty($cars)) {
      throw new NotFoundException();
    }

    return $cars[0];
  }

  public function getAllNotRented() {
    $query = 'SELECT * FROM cars WHERE checkedoutby IS NULL ORDER BY make';
    $sth = $this->db->prepare($query);
    $sth->execute();

    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function getAllRented() {
    $query = 'SELECT * FROM cars WHERE checkedoutby IS NOT NULL ORDER BY make';
    $sth = $this->db->prepare($query);
    $sth->execute();

    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function getAll() {
    $query = 'SELECT * FROM cars';
    $sth = $this->db->prepare($query);
    $sth->execute();

    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function addCar($car) {
    $query = <<<SQL
INSERT INTO cars(registration, make, color, year, price, checkedoutby, checkedouttime)
VALUES(:registration, :make, :color, :year, :price, null, null)
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $car["registration"], PDO::PARAM_STR);
    $sth->bindParam("make", $car["make"], PDO::PARAM_STR);
    $sth->bindParam("color", $car["color"], PDO::PARAM_STR);
    $sth->bindParam("year", $car["year"], PDO::PARAM_INT);
    $sth->bindParam("price", $car["price"], PDO::PARAM_INT);
    $sth->execute();

    return $this->get($car["registration"]);
  }

  public function deleteCar($registration) {
    $query = "DELETE FROM cars WHERE registration=:registration";

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    $sth->execute();
  }

  public function editCar($car) {
    $query = <<<SQL
UPDATE cars
SET make=:make, color=:color, year=:year, price=:price
WHERE registration=:registration
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $car["registration"], PDO::PARAM_STR);
    $sth->bindParam("make", $car["make"], PDO::PARAM_STR);
    $sth->bindParam("color", $car["color"], PDO::PARAM_STR);
    $sth->bindParam("year", $car["year"], PDO::PARAM_INT);
    $sth->bindParam("price", $car["price"], PDO::PARAM_INT);
    $sth->execute();

    return $this->get($car["registration"]);
  }

  public function getMakes() {
    $query = 'SELECT * FROM makes';
    $sth = $this->db->prepare($query);
    $sth->execute();

    $makes = $sth->fetchAll();
 
    return $this->flatten($makes);
  }

  public function getColors() {
    $query = 'SELECT * FROM colors';
    $sth = $this->db->prepare($query);
    $sth->execute();

    $colors = $sth->fetchAll();
 
    return $this->flatten($colors);
  }

  private function flatten($arr) {
    $num = count($arr);
    $newArr = [];

    for($count = 0; $count < $num; $count++) {
      $newArr[$count] =  $arr[$count][0];
    }

    return $newArr;
  }

}