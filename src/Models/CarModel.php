<?php

namespace Main\Models;

use Main\Domain\Book;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;

class CarModel extends AbstractModel {
  const CLASSNAME = '\Main\Domain\Car';

  public function get($registration) {
    $query = 'SELECT * FROM cars WHERE registration = :registration';
    $sth = $this->db->prepare($query);
    $sth->execute(['registration' => $registration]);

    $cars = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    if (empty($cars)) {
      throw new NotFoundException();
    }

    return $cars[0];
  }

  public function getAll($page, $pageLength) {
    $start = $pageLength * ($page - 1);

    $query = 'SELECT * FROM cars LIMIT :page, :length';
    $sth = $this->db->prepare($query);
    $sth->bindParam('page', $start, PDO::PARAM_INT);
    $sth->bindParam('length', $pageLength, PDO::PARAM_INT);
    $sth->execute();

    return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function addCar($car) {
    $query = <<<SQL
INSERT INTO cars(registration, make, color, year, price, checkedoutby, checkedouttime)
VALUES(:registration, :make, :color, :year, :price, null, null)
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $car["registration"], PDO::PARAM_STR);
    $sth->bindParam("make", $car["make"], PDO::PARAM_STR);
    $sth->bindParam("color", $car["color"], PDO::PARAM_STR);
    $sth->bindParam("year", $car["year"], PDO::PARAM_INT);
    $sth->bindParam("price", $car["price"], PDO::PARAM_INT);
    $sth->execute();

    return $this->get($car["registration"]);
  }

  public function deleteCar($registration) {
    $query = "DELETE FROM cars WHERE registration=:registration";

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $registration, PDO::PARAM_STR);
    $sth->execute();
  }

  public function editCar($car) {
    $query = <<<SQL
UPDATE cars
SET make=:make, color=:color, year=:year, price=:price
WHERE registration=:registration
SQL;

    $sth = $this->db->prepare($query);
    $sth->bindParam("registration", $car["registration"], PDO::PARAM_STR);
    $sth->bindParam("make", $car["make"], PDO::PARAM_STR);
    $sth->bindParam("color", $car["color"], PDO::PARAM_STR);
    $sth->bindParam("year", $car["year"], PDO::PARAM_INT);
    $sth->bindParam("price", $car["price"], PDO::PARAM_INT);
    $sth->execute();

    return $this->get($car["registration"]);
  }

  public function getMakes() {
    $query = 'SELECT * FROM makes';
    $sth = $this->db->prepare($query);
    $sth->execute();

    $makes = $sth->fetchAll();
 
    return $this->flatten($makes);
  }

  public function getColors() {
    $query = 'SELECT * FROM colors';
    $sth = $this->db->prepare($query);
    $sth->execute();

    $colors = $sth->fetchAll();
 
    return $this->flatten($colors);
  }

  private function flatten($arr) {
    $num = count($arr);
    $newArr = [];

    for($count = 0; $count < $num; $count++) {
      $newArr[$count] =  $arr[$count][0];
    }

    return $newArr;
  }














  public function getByUser(int $userId): array {
      $query = <<<SQL
  SELECT b.*
  FROM borrowed_books bb LEFT JOIN book b ON bb.book_id = b.id
  WHERE bb.customer_id = :id
  SQL;
      $sth = $this->db->prepare($query);
      $sth->execute(['id' => $userId]);

      return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }

  public function borrow(Book $book, int $userId) {
      $query = <<<SQL
  INSERT INTO borrowed_books (book_id, customer_id, start)
  VALUES(:book, :user, NOW())
  SQL;
      $sth = $this->db->prepare($query);
      $sth->bindValue('book', $book->getId());
      $sth->bindValue('user', $userId);
      if (!$sth->execute()) {
          throw new DbException($sth->errorInfo()[2]);
      }

      $this->updateBookStock($book);
  }

  public function returnBook(Book $book, int $userId) {
      $query = <<<SQL
  UPDATE borrowed_books SET end = NOW()
  WHERE book_id = :book AND customer_id = :user AND end IS NULL
  SQL;
      $sth = $this->db->prepare($query);
      $sth->bindValue('book', $book->getId());
      $sth->bindValue('user', $userId);
      if (!$sth->execute()) {
          throw new DbException($sth->errorInfo()[2]);
      }

      $this->updateBookStock($book);
  }

  private function updateBookStock(Book $book) {
      $query = 'UPDATE book SET stock = :stock WHERE id = :id';
      $sth = $this->db->prepare($query);
      $sth->bindValue('id', $book->getId());
      $sth->bindValue('stock', $book->getStock());
      if (!$sth->execute()) {
          throw new DbException($sth->errorInfo()[2]);
      }
  }

  public function search(string $title, string $author): array {
      $query = <<<SQL
  SELECT * FROM book
  WHERE title LIKE :title AND author LIKE :author
  SQL;
      $sth = $this->db->prepare($query);
      $sth->bindValue('title', "%$title%");
      $sth->bindValue('author', "%$author%");
      $sth->execute();

      return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
  }
}