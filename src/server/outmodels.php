<?php

namespace dvegasa\cpfinal\server\outmodels;

use Cake\Chronos\ChronosInterface;

class OutAccount {
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

abstract class OutQuestion {}

class OutQuestionAnswerInput extends OutQuestion {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $answers, // string[]
            public int $reward,
    ) {}
}

class OutQuestionMultiChoice extends OutQuestion {
    function __construct (
            public string $id,
            public string $title,
            public array $variants, // string[]
            public array $corrects, // string[]
            public int $reward,
    ) {}
}

class OutTest {
    function __construct (
            public string $id,
            public string $title,
            public array $questions, // OutQuestion[]
    ) {}
}

class OutEvent {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public ChronosInterface $timestamp,
            public array $accounts, // OutAccount[]
    ) {}
}

class OutLPType {
    const NORMAL = 'normal';
    const EXAM = 'normal';
}

class OutLP {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $linkedAccounts, // OutAccount[]
            public array $tests, // OutTest[]
            public array $events, // OutEvent[]
            public string $type, // enum OutLPType
            public int $price,
            public array $linked, // string[] of LP (id)
            public ?int $x,
            public ?int $y,
    ) {}
}

class OutArch {
    function __construct (
            public string $id,
            public string $title,
            public string $description,
            public array $lps, // OutLP[]
    ) {}
}

class OutArchNode {
    function __construct (
            public string $id,
            public OutArch $arch,
            public array $nextArch, // OutArch[]
    ) {}
}

class OutOnboardingRoute {
    function __construct (
            public string $id,
            public OutAccount $account,
            public array $archs, // OutArch[]
            public OutArch $startArch,
    ) {}
}
