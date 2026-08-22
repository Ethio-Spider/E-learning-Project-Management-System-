<?php
/**
 * Minimal automated test runner for critical production features.
 */

declare(strict_types=1);

require_once __DIR__ . '/../classes/TwoFactorService.php';
require_once __DIR__ . '/../classes/RateLimiter.php';

$tests = [];

$tests[] = function (): void {
    $secret = TwoFactorService::generateSecret();
    $code = TwoFactorService::generateCode($secret);
    if (!TwoFactorService::verifyCode($secret, $code)) {
        throw new RuntimeException('TOTP verification failed.');
    }
};

$tests[] = function (): void {
    $file = __DIR__ . '/../logs/test_rate_limits_' . uniqid('', true) . '.json';
    if (file_exists($file)) {
        unlink($file);
    }

    $limiter = new RateLimiter(null, $file);
    $result = $limiter->allow('unit-test', '/api/test', 2, 60);
    if (!$result['allowed']) {
        throw new RuntimeException('Rate limiter rejected the first request unexpectedly.');
    }
    $second = $limiter->allow('unit-test', '/api/test', 2, 60);
    if (!$second['allowed']) {
        throw new RuntimeException('Rate limiter rejected the second request unexpectedly.');
    }
    $third = $limiter->allow('unit-test', '/api/test', 2, 60);
    if ($third['allowed']) {
        throw new RuntimeException('Rate limiter should block the third request.');
    }
};

$passed = 0;
foreach ($tests as $index => $test) {
    try {
        $test();
        $passed++;
        echo "PASS: test " . ($index + 1) . PHP_EOL;
    } catch (Throwable $e) {
        echo "FAIL: test " . ($index + 1) . ' - ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

echo "Summary: {$passed}/" . count($tests) . " tests passed." . PHP_EOL;
