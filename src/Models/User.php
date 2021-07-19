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
        return password_verify($password, $this->password);
    }

    public function hashPassword(): self
    {
        $hash = password_hash($this->password, PASSWORD_BCRYPT, [ 'cost' => 12 ]);
        $this->password = $hash;

        return $this;
    }
}
