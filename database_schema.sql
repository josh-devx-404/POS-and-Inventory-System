-- -------------------------------
-- DATABASE CREATION
-- -------------------------------
CREATE DATABASE IF NOT EXISTS makys_cafe;
USE makys_cafe;

-- -------------------------------
-- CATEGORIES
-- -------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);


CREATE DATABASE IF NOT EXISTS makys_cafe;
USE makys_cafe;

-- -------------------------------
-- Users
-- -------------------------------
CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,         -- store bcrypt/argon2 hash here
  fullname VARCHAR(255) NOT NULL,
  role ENUM('admin','cashier') NOT NULL DEFAULT 'cashier',
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  is_deleted TINYINT(1) NOT NULL DEFAULT 0, -- soft delete flag; 0 = active row, 1 = deleted
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  INDEX idx_username (username),
  INDEX idx_role (role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------
-- MENU ITEMS
-- -------------------------------
CREATE TABLE menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- -------------------------------
-- ITEM SIZES (Hot & Cold)
-- -------------------------------
CREATE TABLE item_sizes (
    size_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    size_name VARCHAR(50) NOT NULL, -- e.g., '12oz Hot', '12oz Cold', etc.
    price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
);

-- -------------------------------
-- ADD-ONS
-- -------------------------------
CREATE TABLE addons (
    addon_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(8,2) NOT NULL
);

-- -------------------------------
-- COOKIES PACKAGES
-- -------------------------------
CREATE TABLE cookies_packages (
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL
);

-- -------------------------------
-- ORDERS
-- -------------------------------
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100),
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL
);

-- -------------------------------
-- ORDER ITEMS
-- -------------------------------
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT,
    size_id INT,
    addon_id INT,
    quantity INT DEFAULT 1,
    price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id),
    FOREIGN KEY (size_id) REFERENCES item_sizes(size_id),
    FOREIGN KEY (addon_id) REFERENCES addons(addon_id)
);

-- -------------------------------
-- INSERT CATEGORIES
-- -------------------------------
INSERT INTO categories (name) VALUES
('Maky\'s Favorites'),
('Silogs'),
('Sandwiches'),
('Cookies'),
('Coffee Based'),
('Milk Based'),
('Frappuccino'),
('Smoothies'),
('Healthy Options'),
('Soda');

-- -------------------------------
-- INSERT MENU ITEMS
-- -------------------------------
INSERT INTO menu_items (category_id, name, price) VALUES
(1, 'House Nachos', 180),
(1, 'Lasagna', 180),
(1, 'Baked Mac', 150),
(1, 'Maky\'s Salad', 150),
(2, 'Tapsilog', 155),
(2, 'Tocilog', 135),
(2, 'Longsilog', 135),
(2, 'Bangsilog', 160),
(2, 'Hungariansilog', 125),
(2, 'Hotsilog', 110),
(2, 'Baconsilog', 145),
(2, 'Spamsilog', 140),
(3, 'Maky\'s Clubhouse Sandwich', 150),
(3, 'Cafe Chicken Sandwich', 120),
(3, 'Ham & Cheese Sandwich', 100),
(3, 'Home Tuna Sandwich', 120),
(3, 'Bacon & Cheese Sandwich', 110),
(3, 'Spam Sandwich', 150),
(3, 'Hungarian Sandwich', 120),
(3, '3 Cheese Panini', 105),
(3, 'Ham & Cheese Panini', 85),
(4, 'Classic Chocolate Chip', 30),
(4, 'Double Choco Chips', 35),
(4, 'Red Velvet', 35),
(4, 'Smores Cookie', 35),
(4, 'Matcha Cookie', 35),
(5, 'Cream Latte', 150),
(5, 'Ice Cream Latte', 150),
(5, 'Sea Scotch Latte', 160),
(5, 'Coffee Jelly Latte', 155),
(5, 'Coco Molatte', 165),
(5, 'Tiramisu Latte', 195),
(5, 'Pistachio Latte', 185),
(5, 'Biscoff Pudding Latte', 185),
(5, 'Americano', 85),
(5, 'Latte', 100),
(5, 'Cappuccino', 95),
(5, 'Cafe Mocha', 115),
(5, 'Peppermint Mocha', 120),
(5, 'White Chocolate Mocha', 120),
(5, 'Caramel Macchiato', 110),
(5, 'Spanish Latte', 110),
(5, 'Cinnamon Latte', 100),
(5, 'Irish Cream Latte', 110),
(5, 'Butterscotch Latte', 110),
(5, 'Brown Sugar Latte', 110),
(5, 'Matcha Espresso Latte', 115),
(5, 'Salted Caramel Latte', 100),
(6, 'Chocolate Latte', 100),
(6, 'Matcha Latte', 100),
(6, 'Pure Matcha Latte', 150),
(6, 'Strawberry Latte', 130),
(6, 'Tea Latte', 120),
(6, 'Strawberry Cocoa Latte', 150),
(6, 'Strawberry Matcha Latte', 150),
(7, 'Mocha Frappuccino', 155),
(7, 'Peppermint Mocha Frappuccino', 160),
(7, 'White Chocolate Mocha Frappuccino', 160),
(7, 'Caramel Frappuccino', 155),
(7, 'Java Chip Frappuccino', 165),
(7, 'Coffee Jelly Frappuccino', 165),
(8, 'Strawberry Creme', 150),
(8, 'Dark Chocolate Creme', 150),
(8, 'Matcha Creme', 150),
(8, 'Cookies & Cream', 150),
(9, 'Shaken Turmeric Lemonade', 120),
(9, 'Shaken Orange Earl Tea', 125),
(9, 'Shaken Hibiscus Lemonade', 130),
(9, 'Hibiscus Tea', 100),
(9, 'Chamomile', 100),
(9, 'Earl Grey', 90),
(9, 'Green Tea', 90),
(9, 'English Breakfast', 90),
(10, 'Watermelon Soda', 80),
(10, 'Peach Soda', 80),
(10, 'Mango Soda', 80),
(10, 'Green Apple Soda', 80);

-- -------------------------------
-- INSERT ITEM SIZES (Hot & Cold, 12oz & 16oz)
-- -------------------------------
INSERT INTO item_sizes (item_id, size_name, price) VALUES
-- Example for Cream Latte
((SELECT item_id FROM menu_items WHERE name='Cream Latte'), '12oz Hot', 150),
((SELECT item_id FROM menu_items WHERE name='Cream Latte'), '12oz Cold', 160),
((SELECT item_id FROM menu_items WHERE name='Cream Latte'), '16oz Hot', 180),
((SELECT item_id FROM menu_items WHERE name='Cream Latte'), '16oz Cold', 190);

-- You can repeat for other drinks similarly

-- -------------------------------
-- INSERT ADD-ONS
-- -------------------------------
INSERT INTO addons (name, price) VALUES
('Switch to Oat', 25),
('Add Milk per oz', 10),
('Whipped Cream', 20),
('Syrup', 15),
('Ice Cream', 20),
('Coffee Jelly', 20),
('Extra Rice', 25),
('Extra Fried Rice', 30),
('Extra Egg', 15),
('Cheese', 15);

-- -------------------------------
-- INSERT COOKIES PACKAGES
-- -------------------------------
INSERT INTO cookies_packages (package_name, description, price, quantity) VALUES
('Bag of 6 Classic Choco Chip', '6 Classic Chocolate Chips', 150, 6),
('Bag of 6 Specialty Cookies', '6 Specialty Cookies', 175, 6),
('Box of 8 Classic Choco Chip', '8 Classic Chocolate Chips', 200, 8),
('Box of 8 Specialty Cookies', '8 Specialty Cookies', 240, 8),
('Box of 9 Assorted', '3 Classic Choco Chip, 6 Specialty Cookies', 250, 9);
