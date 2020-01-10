<?php

namespace Main\Domain;

/**
 * Car object with constructor, getter methods and method to turn
 * object properties into an associative array.
 */
class Car {
  private $registration;
  private $make;
  private $color;
  private $year;
  private $price;
  private $checkedoutby;
  private $checkedouttime;

  public function __construct($specs) {
    $this->registration = $specs["registration"];
    $this->make = $specs["make"];
    $this->color = $specs["color"];
    $this->year = $specs["year"];
    $this->price = $specs["price"];

    // Tests if optional values are set. If not, set properies to NULL.
    if (isset($specs["checkedoutby"])) {
      $this->checkedoutby = $specs["checkedoutby"];
    } else {
      $this->checkedoutby = NULL;
    }

    if (isset($specs["checkedouttime"])) {
      $this->checkedouttime = $specs["checkedouttime"];
    } else {
      $this->checkedouttime = NULL;
    }
  }

  public function getregistration() {
    return $this->registration;
  }

  public function getMake() {
    return $this->make;
  }

  public function getColor() {
    return $this->color;
  }

  public function getYear() {
    return $this->year;
  }

  public function getPrice() {
    return $this->price;
  }

  public function getCheckedOutBy() {
    return $this->checkedoutby;
  }

  public function getCheckedOutTime() {
    return $this->checkedouttime;
  }

  public function setCheckedOutBy($value) {
    return $this->checkedoutby = $value;
  }

  public function setCheckedOutTime($value) {
    return $this->checkedouttime = $value;
  }

  // Method to get object properties and create an associative array.
  public function transformToDatabaseAppropriateArray() {
    $arr["registration"] = $this->registration;
    $arr["make"] = $this->make;
    $arr["color"] = $this->color;
    $arr["year"] = $this->year;
    $arr["price"] = $this->price;

    return $arr;
  }
}
