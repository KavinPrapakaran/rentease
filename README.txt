===============================================================
  RENTEASE – Smart Rental Mediation Marketplace
  Final Year Student Project
===============================================================

PROJECT OVERVIEW:
RentEase is a mediator platform (like BookMyShow) connecting 
bike, car, laptop, and camera owners with renters. Vendors 
upload their items; users browse, compare, and book instantly.

---------------------------------------------------------------
FILES IN THIS PROJECT:
---------------------------------------------------------------
1. index.html           → Main homepage (hero, categories, listings)
2. listings.html        → Browse/filter all rental listings
3. vendor-register.html → 4-step vendor signup form
4. vendor-dashboard.html→ Vendor's dashboard (manage listings, bookings)
5. admin-dashboard.html → Admin panel (approve vendors/listings)
6. database.sql         → Complete MySQL database schema

---------------------------------------------------------------
HOW TO USE (Save to C: Drive):
---------------------------------------------------------------
1. Create folder: C:\RentEase\
2. Copy all .html files into: C:\RentEase\
3. Open index.html in Chrome or any browser — it works!
   (Double-click index.html or drag it into browser)

---------------------------------------------------------------
PAGES & THEIR PURPOSE:
---------------------------------------------------------------

HOMEPAGE (index.html):
  - Hero section with stats
  - Search bar (category, location, duration)
  - 4 Category cards (Bike/Car/Laptop/Camera)
  - Featured listings with Book Now buttons
  - How It Works (4 steps)
  - Why Choose Us (features)
  - Vendor CTA section
  - Testimonials, App section, Footer
  - Login/Signup modal popup

LISTINGS PAGE (listings.html):
  - Sidebar filters (category, price, availability, rating)
  - Product cards grid with Book Now buttons
  - Sort dropdown

VENDOR REGISTER (vendor-register.html):
  - Step 1: Account details
  - Step 2: Business info
  - Step 3: Upload first listing + product images
  - Step 4: Success screen

VENDOR DASHBOARD (vendor-dashboard.html):
  - Sidebar navigation
  - Stats: Earnings, Bookings, Listings, Rating
  - Recent bookings table
  - My Listings panel
  - Add New Listing modal
  - Earnings chart

ADMIN DASHBOARD (admin-dashboard.html):
  - Platform stats (revenue, bookings, vendors, users)
  - Pending vendor approvals (Approve/Reject buttons)
  - Commission summary breakdown
  - Pending listing approvals

---------------------------------------------------------------
DATABASE (database.sql):
---------------------------------------------------------------
Tables:
  - users         (customer accounts)
  - vendors       (vendor accounts with approval status)
  - categories    (bike, car, laptop, camera)
  - listings      (products uploaded by vendors)
  - listing_images(multiple images per listing)
  - bookings      (with commission calculation)
  - reviews       (ratings from verified renters)
  - wishlist      (saved listings per user)
  - notifications (alerts for users/vendors/admin)

Import into MySQL:
  1. Open phpMyAdmin (via XAMPP)
  2. Create database named: rentease
  3. Click Import → select database.sql → Go

---------------------------------------------------------------
BACKEND TECH STACK SUGGESTION (Free & Student-Friendly):
---------------------------------------------------------------
  - PHP (runs with XAMPP — free)
  - MySQL (included with XAMPP — free)
  - XAMPP: https://www.apachefriends.org/ (free download)
  
  How to connect:
    1. Install XAMPP
    2. Put HTML files in C:\xampp\htdocs\rentease\
    3. Create PHP files for: login.php, register.php, 
       book.php, upload_listing.php, admin_approve.php
    4. Connect PHP to MySQL using mysqli or PDO

---------------------------------------------------------------
IMAGE UPLOADS:
---------------------------------------------------------------
Vendors upload images via the Add Listing form.
In production with PHP:
  - Save uploaded images to: /uploads/listings/{listing_id}/
  - Store file paths in listing_images table
  - Display using <img src="uploads/...">

---------------------------------------------------------------
COMMISSION MODEL (Like BookMyShow):
---------------------------------------------------------------
  - Bikes:   10% platform commission
  - Cars:    12% platform commission  
  - Laptops:  8% platform commission
  - Cameras: 10% platform commission
  
  Example: Customer pays ₹1,000 for a bike
    → Vendor earns: ₹900 (90%)
    → Platform earns: ₹100 (10% commission)

---------------------------------------------------------------
FOR YOUR PROJECT REPORT / VIVA:
---------------------------------------------------------------
  Modules:
    1. User Module   — register, login, search, book, review
    2. Vendor Module — register, list items, manage bookings
    3. Admin Module  — verify vendors, approve listings, commissions
  
  Technologies: HTML5, CSS3, JavaScript, PHP, MySQL
  Architecture: 3-tier (Presentation → Business → Database)
  Pattern: Mediator/Marketplace (like BookMyShow, Rentrip.in)

===============================================================
  Good luck with your final year project! 🎓
===============================================================
