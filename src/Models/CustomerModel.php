<?php

namespace Main\Models;

use Main\Domain\Customer;

/**
 * Model for hadling customer data providing a simple interface
 * for the CustomerController. Extends and uses the DataModel
 * for communication with the database.
 */
class CustomerModel extends DataModel {

  /**
   * Get ustomers by calling parent class get method. 
   * 
   * @param { $personnumber = person number for the customer to get.}
   * 
   * @return  { New Customer object.}
   */
  public function get($personnumber) {
    $result = parent::getGeneric([
      "table" => "customers",
      "column" => "personnumber",
      "value" => $personnumber]);

    return new Customer($result);
  }

  /**
   * Get all customers by calling parent class getAll method. 
   * 
   * @return  { Array of new Customer objects.}
   */
  public function getAll() {
    $query = <<<SQL
      SELECT personnumber, name, address, postaladdress, phonenumber,
      IF ((SELECT checkouttime FROM rentals 
      WHERE customers.personnumber = rentals.personnumber 
      AND checkouttime IS NOT NULL AND checkintime IS NULL) 
      IS NOT NULL, TRUE, FALSE) AS renting FROM customers
SQL;
    
    $specs = [
      "table" => "customers",
      "order" => "name"
    ];

    $results = parent::executeQuery($query, $specs);

    foreach($results as $result) {
      $customers[] = new Customer($result);
    }

    return $customers;
  }

  /**
   * Insert (create) new customer in the customers table by calling the parent class
   * create method. 
   * 
   * @param { $customer = customer object to create new row from.}
   * 
   * @return  { Customer object created from the inserted data.}
   */
  public function create($customer) {
    $query = <<<SQL
INSERT INTO customers(personnumber, name, address, postaladdress, phonenumber)
VALUES(:personnumber, :name, :address, :postaladdress, :phonenumber)
SQL;

    $specs = $customer->transformToDatabaseAppropriateArray();
    
    parent::insertOrUpdateGeneric($query, $specs);

    return $this->get($customer->getPersonNumber());
  }

  /**
   * Update (modify) customer in the customers table by calling the parent class
   * edit method. 
   * 
   * @param { $customer = customer object to base the update on.}
   * 
   * @return  { Customer object created from the updated data.}
   */
  public function edit($customer) {
    $query = <<<SQL
UPDATE customers
SET name=:name, address=:address, postaladdress=:postaladdress, phonenumber=:phonenumber
WHERE personnumber=:personnumber
SQL;

    $specs = $customer->transformToDatabaseAppropriateArray();

    parent::insertOrUpdateGeneric($query, $specs);

    return $this->get($customer->getPersonNumber());
  }
  
  /**
   * Delete customer by calling parent class delete method. 
   * 
   * @param { $personnumber = person number for the customer to delete.}
   */
  public function delete($personnumber) {
    parent::deleteGeneric([
      "table" => "customers",
      "column" => "personnumber",
      "value" => $personnumber]);
  }
}
