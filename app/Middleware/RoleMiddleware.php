<?php
declare(strict_types=1);

namespace App\Middleware;

final class RoleMiddleware
{
    public static function allows(array $user, string|array $roles): bool
    {
        $allowed = is_array($roles) ? $roles : [$roles];
        return in_array(strtolower((string) ($user['role'] ?? '')), $allowed, true);
    }

    public static function requireRole(string|array $roles): array
    {
        $user = AuthMiddleware::requireAuth();

        if (!self::allows($user, $roles)) {
            \apiResponse(false, 'You do not have permission to perform this action.', null, 403);
        }

        return $user;
    }
}
