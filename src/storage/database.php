<?php

namespace dvegasa\cpfinal\database;

use Exception;
use PDO;

class Database {
    protected PDO $pdo;

    function __construct () {
        $dbHost = $_ENV['DB_HOST'];
        $dbPort = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPassword = $_ENV['DB_PASSWORD'];

        $this->pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;user=$dbUser;password=$dbPassword");
    }

    function test (): void {
        var_dump($this->pdo->query('SELECT * FROM "test"')->fetchAll());
    }

    /**
     * @throws Exception
     */
    function initMigration (): void {
        $sql = file_get_contents(__DIR__ . '\init.sql');
        if ($sql === false) throw new Exception('Failed to read init.sql file');
        $this->pdo->exec($sql);
    }
}
