<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'username',
        'email',
        'password',
        'token'
    ];

    public function checkPassword(string $password): bool
    {
        $verified = password_verify($password, $this->password);
        if (!$verified)
            throw new \Exception('Wrong password', 400);

        return $verified;
    }

    public function hashPassword(): self
    {
        $hash = password_hash($this->password, PASSWORD_BCRYPT, [ 'cost' => 12 ]);
        $this->password = $hash;

        return $this;
    }
}
