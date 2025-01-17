<?php
$host = 'localhost';
$db = 'tasks_db';
$user = 'root';
$pass = '';
try {

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conectarea la baza de date a eșuat: " . $e->getMessage());
}
?>
