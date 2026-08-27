<?php
declare(strict_types=1);

namespace App\Controllers;

final class AssignmentController
{
    public function __construct(private \AssignmentRepository $assignments)
    {
    }

    public function repository(): \AssignmentRepository
    {
        return $this->assignments;
    }
}
