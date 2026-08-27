<?php
declare(strict_types=1);

namespace App\Controllers;

final class UserController
{
    public function __construct(private \App\Repositories\UserRepository $users)
    {
    }

    public function repository(): \App\Repositories\UserRepository
    {
        return $this->users;
    }
}
