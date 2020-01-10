<?php

namespace Main\Models;

use Main\Domain\Rental;
use Main\Domain\Car;

/**
 * Class for handling primarily transactions with the database on table rentals. 
 * Some complicatded transactions affecting the cars tables are also handled
 * here because they are part of larger operations involving multiple tables.
 */
class RentalModel extends DataModel {
  const CLASSNAME_RENTAL = '\Main\Domain\Rental';
  const CLASSNAME_CAR = '\Main\Domain\Car';

  /**
   * Get a specific rental transaction by id. 
   * 
   * @param { $id = id for the rental transaction to get.}
   * 
   * @return  { New Rental object.}
   */
  public function get($id) {
    $result = parent::getGeneric([
      "table" => "rentals",
      "column" => "id",
      "value" => $id]);

    return new Rental($result);
  }

  /**
   * Get all rentals by calling parent class getAll method. 
   * 
   * @return  { Array of new Rental objects.}
   */
  public function getAll() {
    $results = parent::getAllGeneric([
      "table" => "rentals",
      "order" => "checkouttime"
    ]);

    foreach($results as $result) {
      $rentals[] = new Rental($result);
    }

    return $rentals;
  }

  /**
   * Create and store a new rental transaction.
   * 
   * @param { $personnumber = person number of the customer who is
   *                          renting. 
   *          $registration = licens plate of the rented car.}
   */
  public function createRental($personnumber, $registration) {
    // Create a new rental transaction in the rentals table.
    $query = <<<SQL
INSERT INTO rentals (registration, personnumber, checkouttime, checkintime, days, cost)
VALUES (:registration, :personnumber, CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm"), null, null, null)
SQL;

    $specs = ["personnumber" => $personnumber,
              "registration" => $registration];
    $id = $this->executeInsertOrUpdate($query, $specs);

    return $id;
  }

  /**
   * Close a currently open rental transaction.
   * 
   * @param { $registration = licens plate of the rented car.}
   */
  public function closeRental($registration) {
    //  Get the rental from the rentals table based on registration and set variables.
    $specs = ["table" => "rentals",
              "column" => "registration",
              "value" => $registration];
    $amendBy =" AND checkintime IS NULL";

    $rental = new Rental($this->getGeneric($specs, $amendBy));

    $id = $rental->getID();
            
    //  Get info about the car from the cars table to get the rental price.
    $specs = ["table" => "cars",
              "column" => "registration",
              "value" => $registration];

    $car = new Car(parent::getGeneric($specs));

    $price = $car->getPrice();

    //  Start transaction.
    $this->db->beginTransaction();

    //  Set the checkin time in rentals table.
    $query = <<<SQL
UPDATE rentals 
SET checkintime = CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm")
WHERE id = :id
SQL;

    $specs = ["id" => $id];
    $this->executeInsertOrUpdate($query, $specs);
    
    // Calculate the number of days the car has been rented by using mysql-diff function.
    $query = <<<SQL
SELECT TIMESTAMPDIFF(SECOND, 
  (SELECT checkouttime FROM rentals WHERE id = :id), 
  (SELECT checkintime FROM rentals WHERE id = :id))
SQL;

    $specs = ["id" => $id];
    try {
      $diff = parent::executeQuery($query, $specs)[0][0];
    } catch (\Exception $e) {
      $this->db->rollBack();
      throw $e;
    }

    if($diff <= 86400) {
      $days = 1;
    } else {
      $days = intval($diff / 86400) + 1;
    }
    
    $cost = strval($days * $price);

     // Set number of days rented and total cost in the rentals table.
    $query = <<<SQL
UPDATE rentals 
SET days = :days, cost = :cost
WHERE id = :id
SQL;

    $specs = ["id" => $id,
              "days" => $days,
              "cost" => $cost];
    $this->executeInsertOrUpdate($query, $specs);
    
    // Commit all transactions and return id from rentals table.
    $this->db->commit();

    return $id;  
  }

  /**
   * Function to remove a specific customers person number from the rentals table
   * by calling parent class generic removeOccurance method.
   */
  public function removeCustomer($personnumber) {
    parent::removeOccurance([
      "table" => "rentals",
      "column" => "personnumber",
      "value" => $personnumber]);
  }

  /**
   * Function to remove a specific cars registration number from the rentals table
   * by calling parent class generic removeOccurance method.
   */
  public function removeCar($registration) {
    parent::removeOccurance([
      "table" => "rentals",
      "column" => "registration",
      "value" => $registration]);
  }

  /**
   * Helper function to avoid repetition. Use within transaction to be able to
   * rollback any changes if something throws an error. 
   * 
   * @param { $query = the query passed on from the calling function. 
   *          $specs = the specs passed on from the calling function.}
   */
  public function executeInsertOrUpdate($query, $specs) {
    try {
      $id = parent::insertOrUpdateGeneric($query, $specs);
    } catch (\Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
    return $id;
  }
}