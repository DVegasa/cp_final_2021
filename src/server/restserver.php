<?php

namespace dvegasa\cpfinal\server\restserver;

use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Cake\Chronos\ChronosInterval;
use DateTimeImmutable;
use dvegasa\cpfinal\server\outmodels\OutAccount;
use dvegasa\cpfinal\server\outmodels\OutArch;
use dvegasa\cpfinal\server\outmodels\OutLP;
use dvegasa\cpfinal\server\outmodels\OutOnboardingRoute;
use dvegasa\cpfinal\server\outmodels\OutQuestionAnswerInput;
use dvegasa\cpfinal\server\outmodels\OutQuestionMultiChoice;
use dvegasa\cpfinal\server\outmodels\OutTest;
use dvegasa\cpfinal\storage\database\Database;
use dvegasa\cpfinal\storage\dbmodels\DbArch;
use Exception;
use JsonSchema\Exception\ResourceNotFoundException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\JwtAuthentication;

class RestServer {
    function __construct (public Database $db) {
        $app = AppFactory::create();
        $this->setupRouting($app);
        $this->setupAuth($app);
        $app->addErrorMiddleware(true, false, true);
        $app->run();
    }

    protected function setupRouting (App $app): void {
        $app->group('/api', function(RouteCollectorProxy $api) {
            $api->get('/ping', array($this, 'ping'));
            $api->get('/pingAuth', array($this, 'pingAuth'));
            $api->any('/echoBack', array($this, 'echoBack'));
            $api->post('/dbinit', array($this, 'dbinit'));
            $api->post('/auth', array($this, 'auth'));
            $api->get('/onboardingRoute/get', array($this, 'onboardingRoute_get'));
        });
    }

    protected function setupAuth (App $app): void {
        $app->addMiddleware(new JwtAuthentication(array(
                'secret' => $_ENV['WEB_JWT_SECRET'],
                'ignore' => array('/api/auth', '/api/ping'),
                'algorithm' => array('HS256'),
                'secure' => false,
                'attribute' => 'jwt',
        )));
    }

    protected function response (Response $r, $body=null, $code=200): Response {
        if ($body !== null) {
            $r->getBody()->write(json_encode($body));
        } else {
            $r->getBody()->write('');
        }
        $r = $r->withHeader('Content-Type', 'application/json');
        $r = $r->withStatus($code);
        return $r;
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

    function pingAuth (Request $request, Response $response): Response {
        return $this->response($response, array(
                'status' => 'Auth OK',
        ));
    }

    function echoBack (Request $request, Response $response): Response {
        return $this->response($response, array(
                'get' => $this->getGetParams($request),
                'post' => $this->getPostParams($request),
        ));
    }

    function dbinit (Request $request, Response $response): Response {
        if ($this->getPostParams($request)['confirmation'] === 'yes') {
            try {
                $this->db->initMigration();
                return $this->response($response, array('result' => 'OK'));
            } catch (Exception $e) {
                return $this->response($response, array('error' => $e->getMessage()));
            }
        } else {
            return $this->response($response, array('error' => 'Please confirm your action with post data confirmation = yes (inside json)'));
        }
    }


    function auth (Request $request, Response $response): Response {
        $params = $this->getPostParams($request);
        if (!isset($params['email']) || !isset($params['pass'])) {
            return $this->response($response, array('You must specify [email] and [pass] fields'), code: 400);
        }
        $dbAccount = $this->db->getAccountByEmail($params['email']);
        if ($dbAccount === null) return $this->response($response, array('This email is not registered'), code: 400);

        $jwt = $this->issueJwt(Chronos::now()->addDay(), $dbAccount->id, $dbAccount->email);
        return $this->response($response, array('jwt' => $jwt));
    }

    function onboardingRoute_get (Request $request, Response $response): Response {
        $accountId = $request->getAttribute('jwt')['accId'];
        $dbOnboardRoute = $this->db->getOnboardingRouteByAccountId($accountId);
        $dbOnboardRouteAccountId = $this->db->getAccountById($dbOnboardRoute->accountId);
        $dbOnboardRouteStartArch = $this->db->getArchById($dbOnboardRoute->startArchId);
        $outArchs = array();
        foreach ($dbOnboardRoute->archIds as $archId) {
            $dbArch = $this->db->getArchById($archId);
            $outLps = array();
            foreach ($dbArch->lps as $lpId) {
                if ($lpId === '') continue;
                $dbLp = $this->db->getLPById($lpId);
                $outTests = array();
                foreach ($dbLp->testIds as $testId) {
                    if ($testId === '') continue;
                    $dbTest = $this->db->getTestById($testId);
                    $outQuestions = array();
                    foreach ($dbTest->questionIds as $questionId) {
                        $dbQuestion = $this->db->getDbQuestionAnswerInputById($questionId);
                        if ($dbQuestion) {
                            $outQuestions[] = new OutQuestionAnswerInput(
                                    id: $dbQuestion->id,
                                    title: $dbQuestion->title,
                                    description: $dbQuestion->description,
                                    answers: $dbQuestion->answers,
                                    reward: $dbQuestion->reward,
                            );
                        } else {
                            $dbQuestion = $this->db->getDbQuestionMultiChoiceById($questionId);
                            $outQuestions[] = new OutQuestionMultiChoice(
                                    id: $dbQuestion->id,
                                    title: $dbQuestion->title,
                                    variants: $dbQuestion->variants,
                                    corrects: $dbQuestion->corrects,
                                    reward: $dbQuestion->reward,
                            );
                        }
                    }
                    $outTests[] = new OutTest(
                            id: $dbTest->id,
                            title: $dbTest->title,
                            questions: $outQuestions,
                    );
                }

                $outLps[] = new OutLP(
                        id: $dbLp->id,
                        title: $dbLp->title,
                        description: $dbLp->description,
                        linkedAccounts: array(),
                        tests: $outTests,
                        events: array(),
                        type: $dbLp->type,
                        price: $dbLp->price,
                        x: $dbLp->x,
                        y: $dbLp->y,
                );
            }

            $outArchs[] = new OutArch(
                    id: $dbArch->id,
                    title: $dbArch->title,
                    description: $dbArch->description,
                    lps: $outLps
            );
        }

        $res = new OutOnboardingRoute(
                id: $dbOnboardRoute->id,
                account: new OutAccount(
                        id: $dbOnboardRouteAccountId->id,
                        email: $dbOnboardRouteAccountId->email,
                        pass: $dbOnboardRouteAccountId->pass,
                        firstName: $dbOnboardRouteAccountId->firstName,
                        lastName: $dbOnboardRouteAccountId->lastName,
                        position: $dbOnboardRouteAccountId->position,
                        score: $dbOnboardRouteAccountId->score,
                ),
                startArch: new OutArch(
                        id: $dbOnboardRouteStartArch->id,
                        title: $dbOnboardRouteStartArch->title,
                        description: $dbOnboardRouteStartArch->description,
                        lps: $dbOnboardRouteStartArch->lps,
                ),
                archs: $outArchs,
        );
        return $this->response($response, array($res));
    }

    /**
     * @throws Exception
     */
    protected function issueJwt(
            ChronosInterface $expAt,
            string $accountId,
            string $email,
    ): string {
        $now = Chronos::now();
        $jwtCfg = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($_ENV['WEB_JWT_SECRET']));
        $jwt = $jwtCfg->builder()
                ->issuedBy('dvegasa/cpfinal2021')
                ->permittedFor('dvegasa/cpfinal2021')
                ->relatedTo($accountId)
                ->withClaim('accEmail', $email)
                ->withClaim('accId', $accountId)
                ->issuedAt($now->sub(new ChronosInterval(years: 0, seconds: 2)))
                ->expiresAt(new DateTimeImmutable($expAt->toIso8601String()))
                ->getToken($jwtCfg->signer(), $jwtCfg->signingKey());
        return $jwt->toString();
    }
}


