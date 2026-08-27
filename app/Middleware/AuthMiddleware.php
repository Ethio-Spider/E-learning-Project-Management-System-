<?php
declare(strict_types=1);

namespace App\Middleware;

final class AuthMiddleware
{
    public static function user(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function requireAuth(): array
    {
        $user = self::user();
        if ($user === null) {
            \apiResponse(false, 'Authentication required.', null, 401);
        }

        return $user;
    }
}
