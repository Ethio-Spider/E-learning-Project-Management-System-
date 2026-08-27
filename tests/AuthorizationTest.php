<?php
declare(strict_types=1);

use App\Middleware\RoleMiddleware;
use PHPUnit\Framework\TestCase;

final class AuthorizationTest extends TestCase
{
    public function testStudentCannotGrade(): void
    {
        self::assertFalse(RoleMiddleware::allows(['role' => 'student'], ['instructor', 'admin']));
    }

    public function testInstructorCannotAccessAdminFunctions(): void
    {
        self::assertFalse(RoleMiddleware::allows(['role' => 'instructor'], 'admin'));
    }

    public function testAdminHasAdminPermissions(): void
    {
        self::assertTrue(RoleMiddleware::allows(['role' => 'admin'], 'admin'));
    }
}
