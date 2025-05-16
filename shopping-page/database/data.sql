CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    location VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    alt_phone VARCHAR(15),
    pincode VARCHAR(10) NOT NULL,
    state VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    house_no VARCHAR(50) NOT NULL,
    building_name VARCHAR(100) NOT NULL,
    road_name VARCHAR(100) NOT NULL,
    area_name VARCHAR(100) NOT NULL,
    landmark VARCHAR(100),
    address_type ENUM('Home', 'Work') NOT NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    order_id VARCHAR(255) NOT NULL,
    transaction_number VARCHAR(255) NOT NULL,
    size VARCHAR(10) NOT NULL DEFAULT 'M',
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,    
    quantity INT NOT NULL,
    address TEXT NOT NULL, -- Store the full address as a single column
    payment_method VARCHAR(50) NOT NULL,
    final_amount DECIMAL(10, 2) NOT NULL,
    product_image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE orders DROP COLUMN total;

ALTER TABLE orders DROP COLUMN price;

-- Removed conflicting ADD and DROP for 'size' in 'products'

CREATE TABLE adminuser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Use AUTO_INCREMENT for auto-increment in MySQL
    name VARCHAR(255) NOT NULL, -- Use VARCHAR for variable-length strings in MySQL
    old_price DECIMAL(10, 2) NOT NULL,
    new_price DECIMAL(10, 2) NOT NULL,
    details TEXT NOT NULL, -- Use TEXT for large text in MySQL
    image VARCHAR(255), -- Use VARCHAR for strings
    category VARCHAR(100) NOT NULL,
    stock INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP -- Use CURRENT_TIMESTAMP for default timestamp in MySQL
);


CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);


ALTER TABLE products 
ADD COLUMN brand_name VARCHAR(255) NOT NULL AFTER stock;


ALTER TABLE addresses ADD COLUMN user_id INT NOT NULL AFTER id;
CREATE TABLE IF NOT EXISTS deliveryuser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);
ALTER TABLE addresses ADD COLUMN username VARCHAR(255) NOT NULL AFTER id;


CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at DATETIME NOT NULL
);

CREATE TABLE support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    reply TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    replied_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
);

ALTER TABLE orders
ADD COLUMN status VARCHAR(50) DEFAULT 'cancel';


ALTER TABLE orders
ADD COLUMN delivery_status VARCHAR(50) DEFAULT 'Pending';


ALTER TABLE orders ADD COLUMN order_date DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE orders
ADD COLUMN delivery_notes TEXT DEFAULT NULL;

ALTER TABLE orders
ADD COLUMN delivery_time DATETIME DEFAULT NULL;

ALTER TABLE orders
ADD COLUMN delivery_time DATETIME DEFAULT NULL;


CREATE TABLE delivery_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    updated_by VARCHAR(255),
    previous_status VARCHAR(100),
    new_status VARCHAR(100),
    note TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);



ALTER TABLE orders ADD COLUMN cancel_reason TEXT NULL;


CREATE TABLE customercareuser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE customercareuser
ADD COLUMN password VARCHAR(255) NOT NULL AFTER phone;

ALTER TABLE support_messages
ADD COLUMN status ENUM('pending', 'replied', 'resolved') NOT NULL DEFAULT 'pending' AFTER replied_at;
