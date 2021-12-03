<?php
namespace dvegasa\cpfinal\main;

use Dotenv\Dotenv;
use dvegasa\cpfinal\server\restserver\RestServer;
use dvegasa\cpfinal\storage\database\Database;

function main (array $args): void {
    loadEnvVars();
    $db = new Database();

    $restServer = new RestServer($db);
}

function loadEnvVars() {
    Dotenv::createImmutable(__DIR__ . '\..')->load();
}


