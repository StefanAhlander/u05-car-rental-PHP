<?php

namespace Main\Models;

use Main\Domain\Customer;
use Main\Model\RentalModel;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;

class CustomerModel extends AbstractModel {
  const CLASSNAME = "\Main\Domain\Customer";

  public function getCustomer($personnumber) {
    $query = "SELECT * FROM customers WHERE personnumber=:personnumber";

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    $sth->execute();

    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME)[0];
  }

  public function getAll($page, $pageLength) {
    $start = $pageLength * ($page - 1);

    $query = "SELECT * FROM customers LIMIT :page, :length";
    $sth = $this->db->prepare($query);
    $sth->bindParam("page", $start, PDO::PARAM_INT);
    $sth->bindParam("length", $pageLength, PDO::PARAM_INT);
    $sth->execute();

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
    $sth->execute();

    return $this->getCustomer($customer["personnumber"]);
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
    $sth->execute();

    return $this->getCustomer($customer["personnumber"]);
  }
  
  public function deleteCustomer($personnumber) {
    $query = "DELETE FROM customers WHERE personnumber=:personnumber";

    $sth = $this->db->prepare($query);
    $sth->bindParam("personnumber", $personnumber, PDO::PARAM_INT);
    $sth->execute();
  }
}
