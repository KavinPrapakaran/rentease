-- ================= DATABASE =================
CREATE DATABASE IF NOT EXISTS rentease;
USE rentease;

-- ================= CUSTOMERS =================
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================= VENDORS =================
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    business_name VARCHAR(150),
    city VARCHAR(100),
    area VARCHAR(100),
    full_address TEXT,
    aadhar_pan VARCHAR(100),
    bank_account VARCHAR(100),
    ifsc_code VARCHAR(50),
    about TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================= CATEGORIES =================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

INSERT INTO categories (name) VALUES
('bike'),
('car'),
('laptop'),
('camera');

-- ================= LISTINGS =================
CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT,
    category_id INT,
    title VARCHAR(150),
    description TEXT,
    price_per_day DECIMAL(10,2),
    security_deposit DECIMAL(10,2) DEFAULT 0,
    city VARCHAR(100),
    area VARCHAR(100),
    cover_image TEXT,
    availability ENUM('available','booked') DEFAULT 'available',
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ================= LISTING IMAGES =================
CREATE TABLE listing_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    image_path TEXT,
    is_cover BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,

    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- ================= BOOKINGS =================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(20),
    customer_id INT,
    listing_id INT,
    vendor_id INT,

    start_date DATE,
    end_date DATE,
    total_days INT,

    price_per_day DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    platform_fee DECIMAL(10,2),
    security_deposit DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    vendor_payout DECIMAL(10,2),

    payment_method VARCHAR(50),
    special_note TEXT,

    status ENUM('confirmed','completed','cancelled') DEFAULT 'confirmed',
    payment_status ENUM('paid','pending') DEFAULT 'paid',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (vendor_id) REFERENCES vendors(id)
);

-- ================= NOTIFICATIONS =================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','vendor','customer'),
    user_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);