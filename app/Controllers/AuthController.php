<?php
declare(strict_types=1);

namespace App\Controllers;

final class AuthController
{
    public function __construct(private \App\Services\AuthService $auth)
    {
    }

    public function service(): \App\Services\AuthService
    {
        return $this->auth;
    }
}
