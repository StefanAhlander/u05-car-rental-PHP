<?php

namespace Main\Models;

use Main\Domain\Customer;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;

class CustomerModel extends AbstractModel {
  const CLASSNAME = "\Main\Domain\Customer";

  public function get($personnumber) {
    $query = "SELECT * FROM customers WHERE personnumber=:personnumber";

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    $sth->execute();

    $customers = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    if (empty($customers)) {
      throw new NotFoundException('Customer not found.');
    }

    return $customers[0];
  }

  public function getAll() {
    $query = "SELECT * FROM customers ORDER BY name";
    $sth = $this->db->prepare($query);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
    
    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function createCustomer($customer) {
    $query = <<<SQL
INSERT INTO customers(personnumber, name, address, postaladdress, phonenumber, renting)
VALUES(:personnumber, :name, :address, :postaladdress, :phonenumber, false)
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $customer["personnumber"], PDO::PARAM_INT);
    $sth->bindParam("name", $customer["name"], PDO::PARAM_STR);
    $sth->bindParam("address", $customer["address"], PDO::PARAM_STR);
    $sth->bindParam("postaladdress", $customer["postaladdress"], PDO::PARAM_STR);
    $sth->bindParam("phonenumber", $customer["phonenumber"], PDO::PARAM_STR);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
    
    return $this->get($customer["personnumber"]);
  }

  public function editCustomer($customer) {
    $query = <<<SQL
UPDATE customers
SET name=:name, address=:address, postaladdress=:postaladdress, phonenumber=:phonenumber
WHERE personnumber=:personnumber
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $customer["personnumber"], PDO::PARAM_INT);
    $sth->bindParam("name", $customer["name"], PDO::PARAM_STR);
    $sth->bindParam("address", $customer["address"], PDO::PARAM_STR);
    $sth->bindParam("postaladdress", $customer["postaladdress"], PDO::PARAM_STR);
    $sth->bindParam("phonenumber", $customer["phonenumber"], PDO::PARAM_STR);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
    
    return $this->get($customer["personnumber"]);
  }
  
  public function deleteCustomer($personnumber) {
    $query = "DELETE FROM customers WHERE personnumber=:personnumber";

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    if (!$sth->execute()) {
      throw new DbException($sth->errorInfo()[2]);
    }
  }
}
