<?php

namespace dvegasa\cpfinal\storage\database;

use dvegasa\cpfinal\storage\dbmodels\DbAccount;
use dvegasa\cpfinal\storage\dbmodels\DbArch;
use dvegasa\cpfinal\storage\dbmodels\DbLP;
use dvegasa\cpfinal\storage\dbmodels\DbOnboardingRoute;
use dvegasa\cpfinal\storage\dbmodels\DbQuestionAnswerInput;
use dvegasa\cpfinal\storage\dbmodels\DbQuestionMultiChoice;
use dvegasa\cpfinal\storage\dbmodels\DbTest;
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

    function getAccountById (string $id): DbAccount|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "Account" WHERE "id" = ?');
        $stmt->execute(array($id));
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

    function getArchById (string $id): DbArch|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "Arch" WHERE "id" = ?');
        $stmt->execute(array($id));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbArch(
                id: $row['id'],
                title: $row['title'],
                description: $row['description'],
                lps: $this->ga($row['lpIds'], ','),
        );
    }

    function getLPById (string $id): DbLP|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "LP" WHERE "id" = ?');
        $stmt->execute(array($id));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbLP(
                id: $row['id'] ?? null,
                title: $row['title'] ?? null,
                description: $row['description'] ?? null,
                linkedAccountIds: $this->ga($row['linkedAccountIds'] ?? null),
                testIds: $this->ga($row['testIds'] ?? null),
                eventIds: $this->ga($row['eventIds'] ?? null),
                type: $row['type'] ?? null,
                price: $row['price'] ?? null,
                x: $row['x'] ?? null,
                y: $row['y'] ?? null,
        );
    }

    function getTestById (string $id): DbTest|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "Test" WHERE "id" = ?');
        $stmt->execute(array($id));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbTest(
                id: $row['id'] ?? null,
                title: $row['title'] ?? null,
                questionIds: $this->ga($row['questionIds'] ?? null) ?? null,
        );
    }

    function getDbQuestionAnswerInputById (string $id): DbQuestionAnswerInput|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "QuestionAnswerInput" WHERE "id" = ?');
        $stmt->execute(array($id));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbQuestionAnswerInput(
                id: $row['id'] ?? null,
                title: $row['title'] ?? null,
                description: $row['description'] ?? null,
                answers: $this->ga($row['answers'] ?? null),
                reward: $row['reward'] ?? null,
        );
    }

    function getDbQuestionMultiChoiceById (string $id): DbQuestionMultiChoice|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "QuestionMultiChoice" WHERE "id" = ?');
        $stmt->execute(array($id));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbQuestionMultiChoice(
                id: $row['id'] ?? null,
                title: $row['title'] ?? null,
                variants: $this->ga($row['variants'] ?? null),
                corrects: $this->ga($row['corrects'] ?? null),
                reward: $row['reward'] ?? null,
        );
    }

    function getOnboardingRouteByAccountId (string $accountId): DbOnboardingRoute|null {
        $stmt = $this->pdo->prepare('SELECT * FROM "OnboardingRoute" WHERE "accountId" = ?');
        $stmt->execute(array($accountId));
        $row = $stmt->fetch();
        if (!isset($row['id'])) return null;
        return new DbOnboardingRoute(
                id: $row['id'],
                accountId: $row['accountId'],
                archIds: $this->ga($row['archIds']),
                startArchId: $row['startArchId'],
        );
    }

    protected function ga (?string $postgresqlArray): array|null {
        if ($postgresqlArray === null) return null;
        return explode(',', substr($postgresqlArray, 1, -1));
    }
}
