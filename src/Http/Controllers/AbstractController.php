<?php

namespace App\Http\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractController
{
    protected Request  $request;
    protected Response $response;
    protected array    $args;

    public function __construct(
        protected string $method
    ){}

    public function call(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            $methodName = $this->method;
            return $this->$methodName();
        } catch (Exception $error) {
            return $this->error(
                $error->getMessage(),
                $error->getCode()
            );
        }
    }

    public function currentUser(): ?User
    {
        $user = null;
        if ($token = $this->getRequest()->getHeaderLine('Authorization')) {
            $token = str_replace('Bearer ', '', $token);
            $user = User::where('token', $token)->first();
        }

        return $user;
    }

    public function json(array $data, int $status = 200): Response
    {
        $response = $this->getResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
        $response->getBody()->write(json_encode($data));

        return $response;
    }

    public function error(string $message, int $status): Response
    {
        return $this->json([
            'error' => true,
            'message' => $message
        ], $status);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getArg(string $name): mixed
    {
        return $this->args[$name];
    }

    public function getParams(): array
    {
        return $this->getRequest()->getQueryParams();
    }

    public function getParam(string $name): mixed
    {
        $params = $this->getParams();
        return $params[$name];
    }

    protected function checkBodyParams(array $body, array $requiredFields): bool
    {
        foreach ($requiredFields as $fieldName) {
            if (!isset($body[$fieldName]))
                throw new \Exception("{$fieldName} field is missing.", 400);
        }

        return true;
    }
}
