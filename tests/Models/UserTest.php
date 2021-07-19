<?php

namespace Tests\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @return User
     */
    public function testPasswordIsCorrectlyHashed()
    {
        $user = new User([ 'password' => 'good-password' ]);
        $user->hashPassword();
        self::assertFalse(password_needs_rehash(
            $user->password,
            PASSWORD_BCRYPT,
            [ 'cost' => 12 ])
        );

        return $user;
    }

    /**
     * @depends testPasswordIsCorrectlyHashed
     * @return User
     */
    public function testPasswordIsVerified(User $user)
    {
        self::assertTrue($user->checkPassword('good-password'));
        return $user;
    }

    /**
     * @depends testPasswordIsVerified
     */
    public function testPasswordIsNotVerified(User $user)
    {
        self::assertFalse($user->checkPassword('wrong-password'));
    }
}
