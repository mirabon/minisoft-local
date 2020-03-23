<?php
/**
 * Created by PhpStorm.
 * User: Vasilij
 * Date: 23.03.2018
 * Time: 22:57
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './vendor/autoload.php';
require './database/db.php';

$container = new \Slim\Container;
$app = new \Slim\App($container);


$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello!!!");

    return $response;
});


$app->run();