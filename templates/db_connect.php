<?php

// Database credentials
$host = 'localhost';
$user = 'root'; 
$password = ''; 
$db_name = 'FitFlex'; 

// Create a connection
$conn = new mysqli($host, $user, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


//return $conn;
?>
