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
}
