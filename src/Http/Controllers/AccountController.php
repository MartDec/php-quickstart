<?php

namespace App\Http\Controllers;

use App\Http\Middlewares\LoggedUserMiddleware;
use App\Router\Router;
use Psr\Http\Message\ResponseInterface as Response;

#[Router('/account')]
class AccountController extends AbstractController
{
    #[Router('/update', Router::HTTP_METHOD_POST, LoggedUserMiddleware::class)]
    public function update(): Response
    {
        $body = $this->getRequest()->getParsedBody();
        $user = $this->currentUser()
        if (!$user)
            throw new \Exception('Impossible to retrieve your account', 401);

        $user->fill($body);
        $user->hashPassword();
        if ($user->save()) {
            return $this->json([
                'error' => false,
                'user' => $user->getAttributes()
            ]);
        }

        throw new \Exception('An error occured while updating your account', 500);
    }

    #[Router('/delete', Router::HTTP_METHOD_DELETE, LoggedUserMiddleware::class)]
    public function delete(): Response
    {
        if ($user = $this->currentUser()) {
            $user->delete();
            return $this->json([
                'error' => false,
                'message' => "User {$user->username} successfully deleted"
            ]);
        }

        throw new \Exception('An error occured while trying to delete your account', 500);
    }
}
