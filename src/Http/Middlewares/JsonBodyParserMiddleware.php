<?php

namespace App\Http\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/json')) {
            $content = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() === JSON_ERROR_NONE)
                $request = $request->withParsedBody($content);
        }

        return $handler->handle($request);
    }
}