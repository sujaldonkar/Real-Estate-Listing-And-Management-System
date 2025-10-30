<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is a seller
function isSeller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

// Check if user is a buyer
function isBuyer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'buyer';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if not seller
function requireSeller() {
    if (!isSeller()) {
        header("Location: index.php");
        exit();
    }
}

// Redirect if not buyer
function requireBuyer() {
    if (!isBuyer()) {
        header("Location: index.php");
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 