<?php

namespace Database;


use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/connection.php';


connect();

if (!Capsule::schema()->hasTable('users')) {
    Capsule::schema()->create('users', function ($table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('username');
        $table->string('password');
        $table->string('token')->unique();
        $table->timestamps();
    });
}