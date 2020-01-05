<?php

namespace Main\Models;

use Main\Domain\Rental;
use Main\Domain\Car;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;
use DateTime;
use DateTimeZone;

class RentalModel extends AbstractModel {
  const CLASSNAME_RENTAL = '\Main\Domain\Rental';
  const CLASSNAME_CAR = '\Main\Domain\Car';

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

  public function getAll() {
    $query = "SELECT * FROM rentals";

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_RENTAL);
  }

  public function createRental($personnumber, $registration) {
    $this->db->beginTransaction();

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

    $this->db->commit();

    return $id;
  }

  public function closeRental($registration) {
    $query = <<<SQL
SELECT * FROM rentals 
WHERE registration = :registration
AND checkintime IS NULL
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $registration, PDO::PARAM_INT);
    $sth->execute();

    $result = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_RENTAL)[0];
   
    if (empty($result)) {
      throw new NotFoundException('Customer not found.');
    }

    $id = $result->getID();
    $registration = $result->getRegistration();
    $personnumber = $result->getPersonNumber();

    $this->db->beginTransaction();

    $sth = $this->db->prepare("SET @id = :id; SET @registration = :registration; SET @personnumber = :personnumber; ");
    $sth->bindParam(':id', $id, PDO::PARAM_INT);
    $sth->bindParam(':registration', $registration, PDO::PARAM_STR);
    $sth->bindParam(':personnumber', $personnumber, PDO::PARAM_INT);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    $query = <<<SQL
UPDATE customers SET renting = false 
WHERE personnumber = @personnumber
SQL;

    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
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
    
    $query = "SELECT * FROM cars WHERE registration = @registration";
    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      $this->db->rollBack();
      throw new DbException($sth->errorInfo()[2]);
    }
    
    $car = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME_CAR)[0];
    $price = $car->getPrice();

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
    
    $this->db->commit();

    return $id;  
  }
}