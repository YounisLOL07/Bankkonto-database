<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set logout message
session_start(); // Start new session for message
$_SESSION['success_message'] = "You have been successfully logged out!";

// Redirect to index page
header("Location: index.php");
exit();
?> 