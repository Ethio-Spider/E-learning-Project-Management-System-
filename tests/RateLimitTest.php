<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RateLimitTest extends TestCase
{
    public function testRequestsAreRejectedAfterLimitIsExceeded(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'learnflow-rate-limit-');
        $limiter = new RateLimiter(null, $file);

        try {
            self::assertTrue($limiter->allow('test-user', '/login', 2, 60)['allowed']);
            self::assertTrue($limiter->allow('test-user', '/login', 2, 60)['allowed']);
            self::assertFalse($limiter->allow('test-user', '/login', 2, 60)['allowed']);
        } finally {
            if (is_string($file) && file_exists($file)) {
                unlink($file);
            }
        }
    }
}
