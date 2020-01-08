<?php

namespace Main\Models;

use Main\Domain\Rental;
use Main\Domain\Car;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;
use DateTime;
use DateTimeZone;

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
   */
  public function get($id) {
    $query = "SELECT * FROM rentals WHERE id = :id";

    $sth = $this->db->prepare($query);
    $sth->bindParam("id", $id, PDO::PARAM_INT);
    $sth->execute();
    $rentals = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_RENTAL);
   
    if (empty($rentals)) {
      throw new NotFoundException('Customer not found.');
    }

    return $rentals[0];
  }

  /**
   * Get all transactions from the rentals table.
   */
  public function getAll() {
    $query = "SELECT * FROM rentals";

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_RENTAL);
  }

  /**
   * Create and store a new rental transaction. Aslo updates the customers and cars tables.
   */
  public function createRental($personnumber, $registration) {
    // Start transaction so all changes can be rolled back if something goes wrong.
    $this->db->beginTransaction();

    // Set renting value as true in the customers table.
    $query = <<<SQL
UPDATE customers SET renting = true 
WHERE personnumber = :personnumber
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }

    // Updates the cars table with information about who and at what time a car was checked out.
    $query = <<<SQL
UPDATE cars SET checkedoutby = :personnumber,
  checkedouttime = CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm")
WHERE registration = :registration
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    // Create a new rental transaction in the rentals table.
    $query = <<<SQL
INSERT INTO rentals (registration, personnumber, checkouttime, checkintime, days, cost)
VALUES (:registration, :personnumber, CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm"), null, null, null)
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    $id = $this->db->lastInsertId();

    // Commit all transactions and return id for the new rental.
    $this->db->commit();

    return $id;
  }

  public function closeRental($registration) {
    //  Get the rental from the rentals table based on registration.
    $query = <<<SQL
SELECT * FROM rentals 
WHERE registration = :registration
AND checkintime IS NULL
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    $sth->execute();

    $result = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_RENTAL)[0];
   
    if (empty($result)) {
      throw new NotFoundException('Rental not found.');
    }

    //  Get information about the rental.
    $id = $result->getID();
    $personnumber = $result->getPersonNumber();
    $registration = $result->getRegistration();

    //  Start transaction.
    $this->db->beginTransaction();

    //  Set variables for repeated use.
    $sth = $this->db->prepare("SET @id = :id; SET @registration = :registration; SET @personnumber = :personnumber; ");
    $sth->bindParam(':id', $id, PDO::PARAM_INT);
    $sth->bindParam(':registration', $registration, PDO::PARAM_STR);
    $sth->bindParam(':personnumber', $personnumber, PDO::PARAM_INT);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    //  If customer is renting less than two cars update customer table to show the customer as not renting.
    $query = "SELECT COUNT(personnumber) FROM rentals WHERE personnumber = @personnumber AND checkintime IS NULL";
    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    $result = $sth->fetch();

    if ($result[0] < 2) {
      $query = <<<SQL
      UPDATE customers SET renting = false 
      WHERE personnumber = @personnumber
      SQL;
      
          $sth = $this->db->prepare($query);
          if (!$sth->execute()) {
            $this->db->rollBack();
            throw new DbException($sth->errorInfo()[2]);
          }
    }
    
    //  Set the car as not being checked out.
    $query = <<<SQL
UPDATE cars SET checkedoutby = NULL,
  checkedouttime = NULL
WHERE registration = @registration
SQL;

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    //  Get info about the car from the cars table to get the rental price.
    $query = "SELECT * FROM cars WHERE registration = @registration";
    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    $car = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_CAR)[0];
    $price = $car->getPrice();

    //  Set the checkin time in rentals table.
    $query = <<<SQL
UPDATE rentals 
SET checkintime = CONVERT_TZ(NOW(),  @@session.time_zone, "Europe/Stockholm")
WHERE id = @id
SQL;

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    // Calculate the number of days the car has been rented by using mysql-diff function.
    $query = <<<SQL
SELECT TIMESTAMPDIFF(SECOND, 
  (SELECT checkouttime FROM rentals WHERE id = @id), 
  (SELECT checkintime FROM rentals WHERE id = @id))
SQL;

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    $diff = $sth->fetch()[0];

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
WHERE id = @id
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("days", $days, PDO::PARAM_INT);
    $sth->bindParam("cost", $cost, PDO::PARAM_STR);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    // Commit all transactions and return id from rentals table.
    $this->db->commit();

    return $id;  
  }

  /**
   * Function to remove a specific customers person number from the rentals table.
   * Called from CustomerController.
   */
  public function removeCustomer($personnumber) {
    $query = <<<SQL
UPDATE rentals 
SET personnumber = NULL
WHERE personnumber = :personnumber
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
  }

  /**
   * Function to remove a specific cars registration number from the rentals table.
   * Called from CarController.
   */
  public function removeCar($registration) {
    $query = <<<SQL
UPDATE rentals 
SET registration = NULL
WHERE registration = :registration
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
  }
}