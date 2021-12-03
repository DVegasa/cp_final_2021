<?php

namespace dvegasa\cpfinal\storage\dbmodels;

use Cake\Chronos\ChronosInterface;

class DbAccount {
    function __construct (
            public string $id,
            public string $email,
            public string $pass,
            public string $firstName,
            public string $lastName,
            public string $position,
            public int $score,
    ) {}
}

abstract class DbQuestion {}

class DbQuestionAnswerInput extends DbQuestion {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $answers, // string[]
            public int $reward,
    ) {}
}

class DbQuestionMultiChoice extends DbQuestion {
    function __construct (
            public string $id,
            public string $title,
            public array $variants, // string[]
            public array $corrects, // string[]
            public int $reward,
    ) {}
}

class DbTest {
    function __construct (
            public string $id,
            public string $title,
            public array $questions, // DbQuestion[]
    ) {}
}

class DbEvent {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public ChronosInterface $timestamp,
            public array $accounts, // DbAccount[]
    ) {}
}

class DbLPType {
    const NORMAL = 'normal';
    const EXAM = 'normal';
}

class DbLP {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $linkedAccounts, // DbAccount[]
            public array $tests, // DbTest[]
            public array $events, // DbEvent[]
            public string $type, // enum LPType
            public int $price,
            public ?int $x,
            public ?int $y,
    ) {}
}

class DbArch {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $lps, // DbLP[]
    ) {}
}

class DbArchNode {
    function __construct (
            public string $id,
            public DbArch $arch,
            public array $nextArch, // DbArch[]
    ) {}
}

class DbOnboardingRoute {
    function __construct (
            public string $id,
            public DbAccount $account,
            public array $archIds, // DbArch[]
            public DbArch $startArch,
    ) {}
}
