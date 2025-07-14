<?php
$pdo = new PDO('mysql:host=localhost;dbname=mydatabase;charset=utf8', 'user', 'password', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
return $pdo;
