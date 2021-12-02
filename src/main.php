<?php
namespace dvegasa\cpfinal\main;

use Dotenv\Dotenv;
use dvegasa\cpfinal\database\Database;

function main (array $args): void {
    loadEnvVars();
    $db = new Database();
    $db->test();
    echo 'Hello, world!';
}

function loadEnvVars() {
    Dotenv::createImmutable(__DIR__ . '\..')->load();
}
