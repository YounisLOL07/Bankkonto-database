<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

try {
  // Create a new PDO instance and connect to the database
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // Set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create table if it doesn't exist
  $sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )";

  // Execute query
  $conn->exec($sql);
} catch(PDOException $e) {
  // Handle PDO exceptions (database-related errors)
  echo "Connection failed: " . $e->getMessage();
}
?>