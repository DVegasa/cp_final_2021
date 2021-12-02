<?php

namespace dvegasa\cpfinal\server\restserver;

use dvegasa\cpfinal\database\Database;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

class RestServer {
    function __construct (public Database $db) {
        $app = AppFactory::create();
        $this->setupRouting($app);
        $app->addErrorMiddleware(true, false, true);
        $app->run();
    }

    protected function setupRouting (App $app): void {
        $app->group('/api', function(RouteCollectorProxy $api) {
            $api->get('/ping', array($this, 'ping'));
            $api->any('/echoBack', array($this, 'echoBack'));
        });
    }

    protected function response (Response $r, $body=null): Response {
        if ($body !== null) {
            $r->getBody()->write(json_encode($body));
        } else {
            $r->getBody()->write('');
        }
        return $r->withHeader('Content-Type', 'application/json');
    }

    protected function getPostParams(Request $req): ?array {
        return json_decode($req->getBody(), true);
    }

    protected function getGetParams(Request $req): ?array {
        return $req->getQueryParams();
    }


    function ping (Request $request, Response $response): Response {
        return $this->response($response, array(
                'status' => 'ok',
        ));
    }

    function echoBack (Request $request, Response $response): Response {
        return $this->response($response, array(
                'get' => $this->getGetParams($request),
                'post' => $this->getPostParams($request),
        ));
    }
}


