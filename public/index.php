<?php

use App\Router\Router;
use Slim\Exception\HttpException;
use Slim\Factory\AppFactory;


require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/connection.php';

try {

    Database\connect();
    $app = AppFactory::create();
    Router::getRoutes($app);
    $app->run();

} catch (HttpException $e) {

    http_response_code($e->getCode());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);

} catch (Exception $e) {

    die($e->getMessage());

}
