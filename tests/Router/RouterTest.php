<?php

namespace Tests\Router;

use App\Http\Controllers\AbstractController;
use App\Http\Middlewares\GuestUserMiddleware;
use App\Router\Router;
use PHPUnit\Framework\TestCase;
use Tests\PHPUnitUtil;

class RouterTest extends TestCase
{
    public function testGetMiddlewaresAsArray()
    {
        $router = new Router('/path', Router::HTTP_METHOD_GET, GuestUserMiddleware::class);
        self::assertIsArray($router->getMiddlewares());
        return $router;
    }

    public function testGetNoMiddlewares()
    {
        $router = new Router('/path');
        self::assertNull($router->getMiddlewares());
    }

    public function testMiddlewareIsUsable()
    {
        $returnVal = PHPUnitUtil::callMethod(
            Router::class,
            'middlewareIsUsable',
            [ GuestUserMiddleware::class ]
        );
        self::assertTrue($returnVal);
    }

    public function testMiddlewareIsNotUsable()
    {
        $returnVal = PHPUnitUtil::callMethod(
            Router::class,
            'middlewareIsUsable',
            [ AbstractController::class ]
        );
        self::assertFalse($returnVal);
    }
}
