<?php
declare(strict_types=1);

namespace App\Middleware;

final class RateLimitMiddleware
{
    public static function enforce(string $identifier, string $endpoint, int $limit = 60): void
    {
        if (!\config('RATE_LIMIT.enabled', true)) {
            return;
        }

        try {
            $limiter = new \RateLimiter(\getDatabase());
            $result = $limiter->allow($identifier, $endpoint, $limit, 60);
            if (!$result['allowed']) {
                \apiResponse(false, 'Too many requests. Please try again later.', ['retry_after' => (int) ($result['reset_in'] ?? 60)], 429);
            }
        } catch (\Throwable $exception) {
            (new \ErrorMonitor())->report($exception, ['endpoint' => $endpoint, 'identifier' => $identifier]);
        }
    }
}
