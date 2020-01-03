DROP database IF EXISTS Cars;
CREATE database Cars CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Cars;

CREATE TABLE customers(
  personnumber BIGINT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  postaladdress VARCHAR(255) NOT NULL,
  phonenumber VARCHAR(10) NOT NULL,
  renting BOOLEAN NOT NULL
) ENGINE=InnoDb;

CREATE TABLE cars(
  registration CHAR(6) PRIMARY KEY,
  make VARCHAR(100) NOT NULL,
  color VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  price INT NOT NULL,
  checkedoutby BIGINT,
  checkedouttime DATETIME,
  FOREIGN KEY (checkedoutby) REFERENCES customers(personnumber)
) ENGINE=InnoDb;

CREATE TABLE rentals(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  registration CHAR(6) NOT NULL,
  personnumber BIGINT NOT NULL,
  checkouttime DATETIME NOT NULL,
  checkintime DATETIME,
  days INT,
  cost FLOAT,
  FOREIGN KEY (registration) REFERENCES cars(registration) ON DELETE CASCADE,
  FOREIGN KEY (personnumber) REFERENCES customers(personnumber) ON DELETE CASCADE
) ENGINE=InnoDb;

INSERT INTO customers (personnumber, name, address, postaladdress, phonenumber, renting) VALUES
  (197302271452, "Stefan Åhlander", "Lapplandsresan 25 B", "75755 Uppsala", "0704979766", false),
  (198007271482, "Maria Erlandsson", "Lapplandsresan 25 B", "75755 Uppsala", "0725100580", false);

INSERT INTO cars (registration, make, color, year, price) VALUES
  ("LSW364", "Toyota", "Gray", 2012, 500),
  ("DOL990", "Toyota", "Red", 2011, 200);

CREATE TABLE makes(
  make VARCHAR(100) NOT NULL
) ENGINE=InnoDb;

CREATE TABLE colors(
  color VARCHAR(100) NOT NULL
) ENGINE=InnoDb;

INSERT INTO makes (make) VALUES
  ("Peugeot"),
  ("Suzuki"),
  ("Fiat"),
  ("Honda"),
  ("Ford"),
  ("Hyundai"),
  ("Renault"),
  ("Toyota"),
  ("Volkswagen"),
  ("Chrystler"),
  ("Volvo"),
  ("Audi"),
  ("BMW"),
  ("Mercedes");

  INSERT INTO colors (color) VALUES
    ("Blue"),
    ("Red"),
    ("Green"),
    ("Yellow"),
    ("Black"),
    ("White"),
    ("Magenta"),
    ("Gray"),
    ("Brown");