<?php
$host = 'localhost';
$user = 'root';  
$pass = '';         
$dbname = 'skillswap_db'; 

$mysqli = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
?>
