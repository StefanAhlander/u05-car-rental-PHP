DROP database IF EXISTS Cars;
CREATE database Cars CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Cars;

CREATE TABLE customers(
  personnumber BIGINT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  postaladdress VARCHAR(255) NOT NULL,
  phonenumber VARCHAR(10) NOT NULL
) ENGINE=InnoDb;

CREATE TABLE cars(
  registration CHAR(6) PRIMARY KEY,
  make VARCHAR(100) NOT NULL,
  color VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  price INT NOT NULL,
  checkedoutby INT,
  checkedouttime DATETIME
) ENGINE=InnoDb;

CREATE TABLE rentals(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  registration CHAR(6) NOT NULL,
  personnumber BIGINT NOT NULL,
  checkouttime DATETIME NOT NULL,
  checkintime DATETIME NOT NULL,
  days INT NOT NULL,
  cost INT NOT NULL,
  FOREIGN KEY (registration) REFERENCES cars(registration),
  FOREIGN KEY (personnumber) REFERENCES customers(personnumber)
) ENGINE=InnoDb;

INSERT INTO customers (personnumber, name, address, postaladdress, phonenumber) VALUES
  (197302271452, "Stefan Åhlander", "Lapplandsresan 25 B", "75755 Uppsala", "0704979766"),
  (198007271482, "Maria Erlandsson", "Lapplandsresan 25 B", "75755 Uppsala", "0725100580");

INSERT INTO cars (registration, make, color, year, price) VALUES
  ("LSW364", "Toyota", "Gray", 2012, 500),
  ("DOL990", "Toyota", "Red", 2011, 200);
