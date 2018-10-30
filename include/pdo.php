<?php
global $db;

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName","$dbUser","$dbPass");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e){
    exit("<div class='failed text-warning'>Couldn't connect to the database: {$e->getMessage()}</div>\n");
}
