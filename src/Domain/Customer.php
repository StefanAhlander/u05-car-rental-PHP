<?php

namespace Main\Domain;

/**
 * Customer object with constructor, getter methods, method to turn
 * object properties into an associative array and methods to
 * set or reset the renting property.
 */
class Customer {
  private $personnumber;
  private $name;
  private $address;
  private $postaladdress;
  private $phonenumber;
  private $renting;

  public function __construct($specs) {
    $this->personnumber = $specs["personnumber"];
    $this->name = $specs["name"];
    $this->address = $specs["address"];
    $this->postaladdress = $specs["postaladdress"];
    $this->phonenumber = $specs["phonenumber"];
    if(isset($specs["renting"])) {
      $this->renting = $specs["renting"];
    } else {
      $this->renting = FALSE;
    }
  }

  public function getPersonNumber() {
    return $this->personnumber;
  }

  public function getName() {
    return $this->name;
  }

  public function getAddress() {
    return $this->address;
  }

  public function getPostalAddress() {
    return $this->postaladdress;
  }

  public function getPhoneNumber() {
    return $this->phonenumber;
  }

  public function getRenting() {
    return $this->renting;
  }

  /**
   * Method to get object properties and create an associative array.
   * For use in ombination with calls to Model-methods.
   */
  public function toArray() {
    $arr["personnumber"] = $this->personnumber;
    $arr["name"] = $this->name;
    $arr["address"] = $this->address;
    $arr["postaladdress"] = $this->postaladdress;
    $arr["phonenumber"] = $this->phonenumber;

    return $arr;
  }
}
