<?php

namespace Main\Models;

use Main\Domain\Rental;
use Main\Domain\Car;
use Exception;

/**
 * Class for handling primarily transactions with the database on table rentals. 
 * Some complicatded transactions affecting the cars and customers tables are also handled
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
   * Aslo updates the customers and cars tables.
   * 
   * @param { $personnumber = person number of the customer who is
   *                          renting. 
   *          $registration = licens plate of the rented car.}
   */
  public function createRental($personnumber, $registration) {
    // Start transaction so all changes can be rolled back if something goes wrong.
    $this->db->beginTransaction();

    // Set renting value as true in the customers table.
    $query = <<<SQL
UPDATE customers SET renting = true 
WHERE personnumber = :personnumber
SQL;

    $specs = ["personnumber" => $personnumber];
    $this->executeInsertOrUpdate($query, $specs);

    // Updates the cars table with information about who and at what time a car was checked out.
    $query = <<<SQL
UPDATE cars SET checkedoutby = :personnumber,
  checkedouttime = CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm")
WHERE registration = :registration
SQL;

    $specs = ["personnumber" => $personnumber,
              "registration" => $registration];
    $this->executeInsertOrUpdate($query, $specs);

    // Create a new rental transaction in the rentals table.
    $query = <<<SQL
INSERT INTO rentals (registration, personnumber, checkouttime, checkintime, days, cost)
VALUES (:registration, :personnumber, CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm"), null, null, null)
SQL;

    $specs = ["personnumber" => $personnumber,
              "registration" => $registration];
    $id = $this->executeInsertOrUpdate($query, $specs);

    // Commit all transactions and return id for the new rental.
    $this->db->commit();

    return $id;
  }

  /**
   * Close a currently open rental transaction.
   * Aslo updates the customers and cars tables.
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
    $personnumber = $rental->getPersonNumber();
    $registration = $rental->getRegistration();
            
    //  Get info about the car from the cars table to get the rental price.
    $specs = ["table" => "cars",
              "column" => "registration",
              "value" => $registration];

    $car = new Car(parent::getGeneric($specs));

    $price = $car->getPrice();

    //  Start transaction.
    $this->db->beginTransaction();

    //  If customer is renting less than two cars update customer table to show the customer as not renting.
    $query = "SELECT COUNT(personnumber) FROM rentals WHERE personnumber = " . $personnumber;

    $specs = ["table" => "rentals",
              "column" => "personnumber",
              "value" => $personnumber];
    $amendBy = " AND checkintime IS NULL";

    $results = parent::executeQuery($query, $specs, $amendBy);

    if (count($results) == 0) {
      throw new Exception("The customer is not registered as renting. RentalModel->closeRental");
    }

    if ($results[0][0] < 2) {
      $query = "UPDATE customers SET renting = false WHERE personnumber = :personnumber";
      
      $specs = ["personnumber" => $personnumber];
      parent::insertOrUpdateGeneric($query, $specs);
    }

    //  Set the car as not being checked out.
    $query = "UPDATE cars SET checkedoutby = NULL, checkedouttime = NULL WHERE registration = :registration";

    $specs = ["registration" => $registration];
    $this->executeInsertOrUpdate($query, $specs);

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