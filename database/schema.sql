-- FredAgriMarket database schema (MySQL 8 / MariaDB 10.4+)
CREATE DATABASE IF NOT EXISTS fredagrimarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fredagrimarket;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  phone         VARCHAR(20)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('buyer','farmer','admin') NOT NULL DEFAULT 'buyer',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE farmers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL UNIQUE,
  farm_name   VARCHAR(150) NOT NULL,
  location    VARCHAR(150) NOT NULL,
  speciality  VARCHAR(150) NOT NULL DEFAULT '',
  bio         TEXT NULL,
  avatar      VARCHAR(8) NOT NULL DEFAULT '👨‍🌾',
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL UNIQUE,
  slug  VARCHAR(80) NOT NULL UNIQUE,
  emoji VARCHAR(8)  NOT NULL DEFAULT '🌿'
) ENGINE=InnoDB;

CREATE TABLE products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  farmer_id   INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price       DECIMAL(10,2) NOT NULL CHECK (price >= 0),
  unit        VARCHAR(40) NOT NULL DEFAULT 'kg',
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  emoji       VARCHAR(8) NOT NULL DEFAULT '🥬',
  rating      DECIMAL(2,1) NOT NULL DEFAULT 4.5,
  is_organic  TINYINT(1) NOT NULL DEFAULT 1,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (farmer_id)   REFERENCES farmers(id)    ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  INDEX idx_products_category (category_id),
  INDEX idx_products_farmer (farmer_id)
) ENGINE=InnoDB;

CREATE TABLE promo_codes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(30) NOT NULL UNIQUE,
  percent_off TINYINT UNSIGNED NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE orders (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number   VARCHAR(30) NOT NULL UNIQUE,
  user_id        INT UNSIGNED NOT NULL,
  subtotal       DECIMAL(10,2) NOT NULL,
  shipping       DECIMAL(10,2) NOT NULL,
  discount       DECIMAL(10,2) NOT NULL DEFAULT 0,
  total          DECIMAL(10,2) NOT NULL,
  promo_code     VARCHAR(30) NULL,
  payment_method ENUM('mpesa','card','cod') NOT NULL,
  payment_status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  status         ENUM('pending','confirmed','delivered','cancelled') NOT NULL DEFAULT 'pending',
  address        VARCHAR(255) NOT NULL,
  city           VARCHAR(100) NOT NULL,
  phone          VARCHAR(20)  NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_orders_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id     INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NULL,
  farmer_id    INT UNSIGNED NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_price   DECIMAL(10,2) NOT NULL,
  quantity     INT UNSIGNED NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  FOREIGN KEY (farmer_id)  REFERENCES farmers(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(190) NOT NULL,
  subject    VARCHAR(150) NOT NULL,
  message    TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
