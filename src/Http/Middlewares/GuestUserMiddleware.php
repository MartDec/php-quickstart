<?php

namespace App\Http\Middlewares;

use App\Models\User;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class GuestUserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationHeader = $request->getHeaderLine('Authorization');
        if (!$authorizationHeader || !$this->getUser($authorizationHeader))
            return $handler->handle($request);

        $response = (new Response(401))->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'error' => true,
            'message' => 'You can\'t access this route'
        ]));

        return $response;
    }

    protected function getUser(string $authorizationHeader)
    {
        $token = str_replace('Bearer ', '', $authorizationHeader);
        return User::where('token', $token)->first();
    }
}
