-- ============================================================
-- Default sample users for RentEase
-- Passwords are bcrypt-hashed (PASSWORD_BCRYPT / cost=10)
-- Compatible with PHP password_verify()
--
-- Customer  →  kavin@gmail.com  /  Kavin@2005
-- Vendor    →  bruce@gmail.com  /  batman
-- ============================================================

-- Remove existing sample accounts before re-inserting
DELETE FROM customers WHERE email = 'kavin@gmail.com';
DELETE FROM vendors   WHERE email = 'bruce@gmail.com';

-- Default customer  (password: Kavin@2005)
INSERT INTO customers (full_name, email, phone, password)
VALUES (
    'Kavin',
    'kavin@gmail.com',
    '9876543210',
    '$2y$10$iUQ.J7ztGHeHwZvc2dBmEeUYEkaRFV1aJi1j/pW//lzgUhUuG7FlW'  -- Kavin@2005
);

-- Default vendor  (password: batman)
INSERT INTO vendors (full_name, email, phone, password, business_name, city, area, full_address, status)
VALUES (
    'Bruce',
    'bruce@gmail.com',
    '9123456780',
    '$2y$10$hgpKT1znoIlYO27sqYqUP.Zq1yZW4EI6vkLMj7vHR6F54AELL5EKO',  -- batman
    'Bruce Rentals',
    'Salem',
    'Town Area',
    'Salem, Tamil Nadu, India',
    'approved'
);
