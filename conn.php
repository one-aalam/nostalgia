<?php

require_once 'config.php';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db;", $user, $password);
    if ($pdo) {
        echo "Connection established successfully!";
        return $pdo;
    }
} catch (PDOException $e) {
    die($e->getMessage());
} finally {
    if ($pdo) {
        $pdo = null;
    }
}
