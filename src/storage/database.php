<?php

namespace dvegasa\cpfinal\storage\database;

use dvegasa\cpfinal\storage\dbmodels\DbAccount;
use dvegasa\cpfinal\storage\dbmodels\DbOnboardingRoute;
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

        $this->pdo = new PDO(
                dsn: "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;user=$dbUser;password=$dbPassword",
                options: array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ),
        );
        $tz = date_default_timezone_get();
        $this->pdo->exec('SET TIME ZONE ' . $this->pdo->quote($tz));
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

    function getAccountByEmail (string $email): DbAccount|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "Account" WHERE "email" = ?');
        $stmt->execute(array($email));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbAccount(
                id: $row['id'],
                email: $row['email'],
                pass: $row['pass'],
                firstName: $row['firstName'],
                lastName: $row['lastName'],
                position: $row['position'],
                score: $row['score'],
        );
    }

    function getOnboardingRouteByAccountId (string $accountId): DbOnboardingRoute|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "OnboardingRoute" WHERE "id" = ?');
        $stmt->execute(array($accountId));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbOnboardingRoute(
                id: $row['id'],
                account: $row['account'],
                archIds: $row['archIds'],
                startArch: $row['startArch'],
        );
    }
}
