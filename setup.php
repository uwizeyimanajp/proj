<?php
// Setup script to initialize the database

require_once 'includes/config.php';

echo "<h1>Database Setup</h1>";

// Create connection without database first
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "<p>Database created successfully or already exists.</p>";
} else {
    echo "<p>Error creating database: " . $conn->error . "</p>";
}

$conn->select_db(DB_NAME);

// Read and execute setup.sql
$sql = file_get_contents('setup.sql');

if ($conn->multi_query($sql)) {
    echo "<p>Database setup completed successfully!</p>";
    echo "<p>Default admin login: username 'admin', password 'admin123'</p>";
    echo "<p><a href='index.php'>Go to Home Page</a></p>";
    echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
} else {
    echo "<p>Error setting up database: " . $conn->error . "</p>";
}

$conn->close();
?>
