<?php

namespace Main\Domain;

/**
 * Rental object with constructor, getter methods and method to turn
 * object properties into an associative array.
 */
class Rental {
  private $id;
  private $registration;
  private $personnumber;
  private $checkouttime;
  private $checkintime;
  private $days;
  private $cost;

  public function __construct($specs) {
    $this->id = $specs["id"];
    $this->registration = $specs["registration"];
    $this->personnumber = $specs["personnumber"];
    $this->checkouttime = $specs["checkouttime"];
    $this->checkintime = $specs["checkintime"];
    $this->days = $specs["days"];
    $this->cost = $specs["cost"];

    // Tests if optional values are set. If not, set properies to NULL.
    if (isset($specs["checkintime"])) {
      $this->checkintime = $specs["checkintime"];
    } else {
      $this->checkintime = NULL;
    }

    if (isset($specs["days"])) {
      $this->days = $specs["days"];
    } else {
      $this->days = NULL;
    }

    if (isset($specs["cost"])) {
      $this->cost = $specs["cost"];
    } else {
      $this->cost = NULL;
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getRegistration() {
    return $this->registration;
  }

  public function getPersonNumber() {
    return $this->personnumber;
  }

  public function getCheckOutTime() {
    return $this->checkouttime;
  }

  public function getCheckInTime() {
    return $this->checkintime;
  }

  public function getDays() {
    return $this->days;
  }

  public function getCost() {
    return $this->cost;
  }
  
  // Method to get object properties and create an associative array.
  public function toArray() {
    $arr["id"] = $this->id;
    $arr["registration"] = $this->registration;
    $arr["personnumber"] = $this->personnumber;
    $arr["checkouttime"] = $this->checkouttime;
    $arr["checkintime"] = $this->checkintime;
    $arr["days"] = $this->days;
    $arr["cost"] = $this->cost;

    return $arr;
  }
}
