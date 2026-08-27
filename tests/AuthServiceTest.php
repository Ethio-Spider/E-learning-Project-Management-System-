<?php
declare(strict_types=1);

use App\Repositories\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testCorrectPasswordAuthenticatesUser(): void
    {
        $users = $this->createMock(UserRepository::class);
        $user = ['id' => 7, 'email' => 'student@example.com', 'role' => 'student'];
        $users->expects($this->once())->method('getByEmail')->willReturn($user);
        $users->expects($this->once())->method('verifyPassword')->with('student@example.com', 'correct')->willReturn(true);

        self::assertSame($user, (new AuthService($users))->authenticate('student@example.com', 'correct'));
    }

    public function testIncorrectPasswordDoesNotAuthenticateUser(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('getByEmail')->willReturn(['id' => 7, 'email' => 'student@example.com']);
        $users->expects($this->once())->method('verifyPassword')->willReturn(false);

        self::assertNull((new AuthService($users))->authenticate('student@example.com', 'incorrect'));
    }

    public function testAuthenticatedUserCreatesSession(): void
    {
        $service = new AuthService($this->createMock(UserRepository::class));

        $sessionUser = $service->createSession([
            'id' => 7,
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        self::assertSame($sessionUser, $_SESSION['user']);
        self::assertSame('student', $_SESSION['role']);
    }

    public function testLogoutClearsSession(): void
    {
        $_SESSION = ['user' => ['id' => 7], 'csrf_token' => 'token'];
        $service = new AuthService($this->createMock(UserRepository::class));

        $service->signOut();

        self::assertSame([], $_SESSION);
    }
}
