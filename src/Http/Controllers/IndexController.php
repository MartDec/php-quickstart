<?php

namespace App\Http\Controllers;


use App\Http\Middlewares\LoggedUserMiddleware;
use App\Router\Router;
use Psr\Http\Message\ResponseInterface as Response;

class IndexController extends AbstractController
{
    #[Router('/')]
    public function index(): Response
    {
        return $this->json([
            'error' => false,
            'message' => 'Hello world!'
        ]);
    }

    #[Router('/test', Router::HTTP_METHOD_GET, LoggedUserMiddleware::class)]
    public function test(): Response
    {
        return $this->json([
            'error' => false,
            'message' => 'Welcome!'
        ]);
    }
}
