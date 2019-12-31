<?php

namespace Main\Domain;

class Customer {
    private $personnumber;
    private $name;
    private $address;
    private $postaladdress;
    private $phonenumber;

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
}
