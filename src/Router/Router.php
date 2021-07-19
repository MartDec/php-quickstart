<?php

namespace App\Router;

use App\Http\Middlewares\JsonBodyParserMiddleware;
use App\Http\Middlewares\AccessControlMiddleware;
use Psr\Http\Server\MiddlewareInterface;
use Slim\App;
use Attribute;
use ReflectionClass;
use ReflectionMethod;
use HaydenPierce\ClassFinder\ClassFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteInterface;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_CLASS)]
class Router
{
    const HTTP_METHOD_GET       = 'get';
    const HTTP_METHOD_POST      = 'post';
    const HTTP_METHOD_DELETE    = 'delete';

    private static array $called = [];

    public function __construct(
        private string  $path,
        private string  $method         = self::HTTP_METHOD_GET,
        private mixed   $middlewares    = null
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getMiddlewares(): mixed
    {
        if (!is_null($this->middlewares) && gettype($this->middlewares) !== 'array')
            return [ $this->middlewares ];

        return $this->middlewares;
    }

    public static function getRoutes(App $app): void
    {
        self::addMiddlewares($app);
        self::findControllers($app);
        self::getOptionsMethodRoutes($app);
    }

    private static function addMiddlewares(App &$app): void
    {
        $app->addMiddleware(new JsonBodyParserMiddleware());
        $app->addMiddleware(new AccessControlMiddleware());
        $app->addRoutingMiddleware();
    }

    private static function getOptionsMethodRoutes(App $app): void
    {
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            $pattern = $route->getPattern();
            if (!in_array($pattern, self::$called)) {
                $app->options($pattern, function (ServerRequestInterface $request, ResponseInterface $response) {
                    return $response;
                });
                self::$called[] = $pattern;
            }
        }
    }

    private static function findControllers($app): void
    {
        $controllers = ClassFinder::getClassesInNamespace('App\Http\Controllers', ClassFinder::RECURSIVE_MODE);
        foreach ($controllers as $controller) {
            $class = new ReflectionClass($controller);
            self::findPublicControllerMethods($app, $class, $controller);
        }
    }

    private static function findPublicControllerMethods(App $app, ReflectionClass $class, string $controller): void
    {
        $prefix = self::getPrefix($class);
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
            self::findMethodsWithRouteAttributes($app, $controller, $method, $prefix);
    }

    private static function getPrefix(ReflectionClass $class)
    {
        $routeAttributes = $class->getAttributes(self::class);
        $prefix = null;
        if (!empty($routeAttributes))
            $prefix = $routeAttributes[0]->newInstance()->getPath();

        return $prefix;
    }

    private static function findMethodsWithRouteAttributes(
        App $app,
        string $controller,
        ReflectionMethod $method,
        ?string $prefix
    ): void
    {
        $attributes = $method->getAttributes(self::class);
        if (!empty($attributes))
            self::callMethods($app, $controller, $method, $prefix, $attributes);
    }

    private static function callMethods(
        App $app,
        string $controller,
        ReflectionMethod $method,
        ?string $prefix,
        array $attributes
    ): void
    {
        foreach ($attributes as $attribute) {
            $router = $attribute->newInstance();
            self::callMethod($app, $controller, $method, $prefix, $router);
        }
    }

    private static function callMethod(
        App $app,
        string $controller,
        ReflectionMethod $method,
        ?string $prefix,
        self $router
    ): void
    {
        $httpMethod = strtolower($router->getMethod());
        $path = $prefix . $router->getPath();
        $route = $app->$httpMethod(
            $path,
            [ new $controller($method->getName()), 'call' ]
        );

        if ($middlewares = $router->getMiddlewares())
            self::addRouteMiddlewares($route, $middlewares);
    }

    private static function addRouteMiddlewares(RouteInterface $route, mixed $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (self::middlewareIsUsable($middleware))
                $route->addMiddleware(new $middleware());
            else
                throw new \Exception('Middleware must be instance of MiddlewareInterface');
        }
    }

    private static function middlewareIsUsable($middlewareClassName): bool
    {
        $class = new ReflectionClass($middlewareClassName);
        foreach ($class->getInterfaces() as $interface) {
            if ($interface->getName() === MiddlewareInterface::class)
                return true;
        }

        return false;
    }
}
