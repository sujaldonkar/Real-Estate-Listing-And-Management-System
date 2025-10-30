<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'real_estate');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(mysqli_query($conn, $sql)){
    mysqli_select_db($conn, DB_NAME);
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        role ENUM('buyer', 'seller') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS properties (
        id INT PRIMARY KEY AUTO_INCREMENT,
        seller_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        property_type ENUM('flat', 'house', 'plot', 'bungalow', 'room') NOT NULL,
        transaction_type ENUM('sale', 'rent') NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        location VARCHAR(255) NOT NULL,
        area DECIMAL(10,2) NOT NULL,
        bedrooms INT,
        bathrooms INT,
        floor INT,
        floors INT,
        furnishing ENUM('furnished', 'semi-furnished', 'unfurnished'),
        parking ENUM('yes', 'no'),
        age INT,
        plot_size DECIMAL(10,2),
        plot_type ENUM('residential', 'commercial', 'agricultural', 'industrial'),
        facing ENUM('north', 'south', 'east', 'west', 'north-east', 'north-west', 'south-east', 'south-west'),
        boundary_wall ENUM('yes', 'no'),
        room_type ENUM('single', 'double', 'shared'),
        bathroom_attached ENUM('yes', 'no'),
        verification_doc VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS property_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        property_id INT,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id)
    )"
];

foreach($tables as $sql) {
    if(!mysqli_query($conn, $sql)) {
        die("ERROR: Could not create table. " . mysqli_error($conn));
    }
}
?> 