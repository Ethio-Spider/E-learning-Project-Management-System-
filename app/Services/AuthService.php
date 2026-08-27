<?php
declare(strict_types=1);

namespace App\Services;

final class AuthService
{
    public function __construct(private \App\Repositories\UserRepository $users)
    {
    }

    public function authenticate(string $email, string $password): ?array
    {
        $normalizedEmail = strtolower(trim($email));
        $user = $this->users->getByEmail($normalizedEmail);
        if ($user === null || !$this->users->verifyPassword($normalizedEmail, $password)) {
            return null;
        }

        return $user;
    }

    public function createSession(array $user): array
    {
        $role = (string) ($user['role'] ?? 'student');
        $_SESSION['user'] = [
            'id' => (int) ($user['id'] ?? 0),
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ucfirst($role) . ' User',
            'email' => $user['email'] ?? '',
            'role' => $role,
        ];
        $_SESSION['role'] = $role;

        return $_SESSION['user'];
    }

    public function signOut(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
