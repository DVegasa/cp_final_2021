<?php
namespace dvegasa\cpfinal\main;

use Dotenv\Dotenv;
use PDO;
use PDOException;

function main (array $args): void {
    loadEnvVars();

    try {
        $dbHost = $_ENV['DB_HOST'];
        $dbPort = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPassword = $_ENV['DB_PASSWORD'];
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;user=$dbUser;password=$dbPassword");
        echo "PDO connection object created";

        var_dump($pdo->query('SELECT * FROM "test"')->fetchAll());
    } catch(PDOException $e) {
        echo $e->getMessage();
    }

    echo 'Hello, world!';
}

function loadEnvVars() {
    Dotenv::createImmutable('../')->load();
}
