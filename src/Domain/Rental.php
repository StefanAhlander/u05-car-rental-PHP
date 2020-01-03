<?php

namespace Main\Domain;

class Rental {
    private $id;
    private $registration;
    private $personnumber;
    private $checkouttime;
    private $checkintime;
    private $days;
    private $cost;

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
}
