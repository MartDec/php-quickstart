<?php

namespace App\Http\Controllers;

use App\Http\Middlewares\GuestUserMiddleware;
use App\Router\Router;
use App\Models\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Dotenv\Dotenv;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;

class SessionController extends AbstractController
{
    #[Router('/register', Router::HTTP_METHOD_POST, GuestUserMiddleware::class)]
    public function register(): Response
    {
        $body = $this->getRequest()->getParsedBody();
        $this->checkBodyParams($body, ['email', 'username', 'password']);

        if (User::where('email', $body['email'])->exists())
            throw new \Exception("User {$body['email']} already exists", 400);

        $body['token'] = $this->generateToken($body['email'])->toString();
        $user = new User($body);
        $user->hashPassword();
        if ($user->save()) {
            return $this->json([
                'error' => false,
                'user' => $user->getAttributes()
            ]);
        }

        throw new \Exception('An error occured while creating yout profile', 500);
    }

    #[Router('/login', Router::HTTP_METHOD_POST, GuestUserMiddleware::class)]
    public function login(): Response
    {
        $body = $this->getRequest()->getParsedBody();
        $this->checkBodyParams($body, ['email', 'password']);
        $user = User::where('email', $body['email'])->first();

        if (!$user)
            throw new \Exception("User {$body['email']} not found", 404);

        $user->checkPassword($body['password']);
        $user->token = $this->generateToken($body['email'])->toString();
        if ($user->save()) {
            return $this->json([
                'error' => false,
                'user' => $user->getAttributes()
            ]);
        }

        throw new \Exception('An error occured while you were trying to login', 500);
    }

    protected function generateToken(string $email): Plain
    {
        $env = (new Dotenv())->parse(file_get_contents(__DIR__ . '/../../../.env'));
        $jwtKey = InMemory::plainText($env['JWT_TOKEN']);
        $config = Configuration::forSymmetricSigner(new Sha256(), $jwtKey);
        $token = $config->builder()
            ->issuedBy($env['CLIENT_BASEPATH'])
            ->issuedAt(new DateTimeImmutable())
            ->withClaim('user_email', $email);

        return $token->getToken($config->signer(), $config->signingKey());
    }
}
