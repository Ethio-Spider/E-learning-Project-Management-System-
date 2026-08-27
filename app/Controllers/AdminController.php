<?php
declare(strict_types=1);

namespace App\Controllers;

final class AdminController
{
    public function __construct(private \App\Repositories\UserRepository $users, private \App\Repositories\CourseRepository $courses)
    {
    }

    public function users(): \App\Repositories\UserRepository
    {
        return $this->users;
    }

    public function courses(): \App\Repositories\CourseRepository
    {
        return $this->courses;
    }
}
