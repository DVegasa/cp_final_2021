<?php
namespace dvegasa\cpfinal\main;

use Dotenv\Dotenv;
use dvegasa\cpfinal\database\Database;
use dvegasa\cpfinal\server\restserver\RestServer;

function main (array $args): void {
    loadEnvVars();
    $db = new Database();

    $restServer = new RestServer($db);
}

function loadEnvVars() {
    Dotenv::createImmutable(__DIR__ . '\..')->load();
}


