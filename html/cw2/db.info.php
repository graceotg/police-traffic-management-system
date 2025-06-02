<?php
// MySQL database information       
$servername = "mariadb";
$username = "root";
$password = "rootpwd";
$dbname = "cw2-database";

// Attempt database connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>