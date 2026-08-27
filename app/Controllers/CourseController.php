<?php
declare(strict_types=1);

namespace App\Controllers;

final class CourseController
{
    public function __construct(private \App\Repositories\CourseRepository $courses)
    {
    }

    public function repository(): \App\Repositories\CourseRepository
    {
        return $this->courses;
    }
}
