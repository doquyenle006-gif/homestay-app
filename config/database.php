<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'homestay_db';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($database);
} else {
    die("Error creating database: " . $conn->error);
}

// Function to execute SQL queries
if (!function_exists('executeQuery')) {
    function executeQuery($sql) {
        global $conn;
        return $conn->query($sql);
    }
}

// Function to escape strings
if (!function_exists('escapeString')) {
    function escapeString($string) {
        global $conn;
        return $conn->real_escape_string($string);
    }
}

// Function to get last insert ID
if (!function_exists('getLastInsertId')) {
    function getLastInsertId() {
        global $conn;
        return $conn->insert_id;
    }
}

// Database connected successfully
?>