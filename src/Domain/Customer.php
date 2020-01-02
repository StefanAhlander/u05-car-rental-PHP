<?php

namespace Main\Domain;

class Customer {
    private $personnumber;
    private $name;
    private $address;
    private $postaladdress;
    private $phonenumber;
    private $renting;

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

    public function setRenting() {
        $this->renting = true;
    }

    public function resetRenting() {
        $this->renting = false;
    }
}
