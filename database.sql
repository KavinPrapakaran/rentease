-- ============================================================
--  SMART RENTAL MEDIATION & INSTANT RIDE MARKETPLACE
--  Complete MySQL Database
--  Import this in phpMyAdmin → Select DB → Import → Choose file
-- ============================================================

CREATE DATABASE IF NOT EXISTS rentease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rentease;

-- ─────────────────────────────────────────────────────────
--  TABLE 1: CUSTOMERS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS customers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(120)        NOT NULL,
    email        VARCHAR(150) UNIQUE NOT NULL,
    phone        VARCHAR(20)         NOT NULL,
    password     VARCHAR(255)        NOT NULL,
    city         VARCHAR(80),
    address      TEXT,
    profile_img  VARCHAR(255)        DEFAULT NULL,
    is_active    TINYINT(1)          DEFAULT 1,
    created_at   DATETIME            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 2: VENDORS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vendors (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(120)        NOT NULL,
    email          VARCHAR(150) UNIQUE NOT NULL,
    phone          VARCHAR(20)         NOT NULL,
    password       VARCHAR(255)        NOT NULL,
    business_name  VARCHAR(180)        NOT NULL,
    city           VARCHAR(80)         NOT NULL,
    area           VARCHAR(100),
    full_address   TEXT,
    aadhar_pan     VARCHAR(60),
    bank_account   VARCHAR(40),
    ifsc_code      VARCHAR(20),
    about          TEXT,
    profile_img    VARCHAR(255)        DEFAULT NULL,
    status         ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at     DATETIME            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 3: ADMINS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: admin@rentease.com / Admin@123
INSERT INTO admins (name, email, password) VALUES
('Super Admin', 'admin@rentease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ─────────────────────────────────────────────────────────
--  TABLE 4: CATEGORIES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    icon VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

INSERT INTO categories (name, icon) VALUES
('bike',   'fa-motorcycle'),
('car',    'fa-car'),
('laptop', 'fa-laptop'),
('camera', 'fa-camera');

-- ─────────────────────────────────────────────────────────
--  TABLE 5: LISTINGS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS listings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id        INT              NOT NULL,
    category_id      INT              NOT NULL,
    title            VARCHAR(220)     NOT NULL,
    description      TEXT,
    price_per_day    DECIMAL(10,2)    NOT NULL,
    security_deposit DECIMAL(10,2)    DEFAULT 0,
    city             VARCHAR(80),
    area             VARCHAR(100),
    availability     ENUM('available','booked','unavailable') DEFAULT 'available',
    status           ENUM('pending','approved','rejected')    DEFAULT 'pending',
    cover_image      VARCHAR(255)     DEFAULT NULL,
    created_at       DATETIME         DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id)   REFERENCES vendors(id)    ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 6: LISTING IMAGES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS listing_images (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT          NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_cover   TINYINT(1)   DEFAULT 0,
    sort_order INT          DEFAULT 0,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 7: BOOKINGS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    booking_code    VARCHAR(20)       NOT NULL UNIQUE,
    customer_id     INT               NOT NULL,
    listing_id      INT               NOT NULL,
    vendor_id       INT               NOT NULL,
    start_date      DATE              NOT NULL,
    end_date        DATE              NOT NULL,
    total_days      INT               NOT NULL,
    price_per_day   DECIMAL(10,2)     NOT NULL,
    subtotal        DECIMAL(10,2)     NOT NULL,
    platform_fee    DECIMAL(10,2)     NOT NULL,
    security_deposit DECIMAL(10,2)    DEFAULT 0,
    total_amount    DECIMAL(10,2)     NOT NULL,
    vendor_payout   DECIMAL(10,2)     NOT NULL,
    payment_method  VARCHAR(50),
    special_note    TEXT,
    status          ENUM('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
    payment_status  ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    created_at      DATETIME          DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id)  REFERENCES listings(id)  ON DELETE CASCADE,
    FOREIGN KEY (vendor_id)   REFERENCES vendors(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 8: REVIEWS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT     NOT NULL UNIQUE,
    customer_id INT    NOT NULL,
    listing_id INT     NOT NULL,
    rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id)  REFERENCES bookings(id)  ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id)  REFERENCES listings(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 9: WISHLIST
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS wishlist (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    listing_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wish (customer_id, listing_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id)  REFERENCES listings(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
--  TABLE 10: NOTIFICATIONS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    role      ENUM('customer','vendor','admin') NOT NULL,
    user_id   INT    NOT NULL,
    message   TEXT   NOT NULL,
    is_read   TINYINT(1) DEFAULT 0,
    created_at DATETIME  DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
