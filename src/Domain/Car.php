<?php

namespace Main\Domain;

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

  public function toArray() {
    $arr["registration"] = $this->registration;
    $arr["make"] = $this->make;
    $arr["color"] = $this->color;
    $arr["year"] = $this->year;
    $arr["price"] = $this->price;
    $arr["checkedoutby"] = $this->checkedoutby;
    $arr["checkedouttime"] = $this->checkedouttime;

    return $arr;
  }
}
