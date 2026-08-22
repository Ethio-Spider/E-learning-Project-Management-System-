<?php
/**
 * ErrorMonitor - captures runtime exceptions and forwards them to configured monitoring service.
 */

declare(strict_types=1);

class ErrorMonitor
{
    private Logger $logger;
    private string $dsn;
    private bool $enabled;

    public function __construct(?Logger $logger = null, ?string $dsn = null, bool $enabled = false)
    {
        $this->logger = $logger ?? new Logger(config('LOGGING.log_dir', __DIR__ . '/../logs/'), config('LOGGING.log_level', 'INFO'));
        $this->dsn = $dsn ?? (string) config('MONITORING.sentry_dsn', '');
        $this->enabled = $enabled || config('MONITORING.error_tracking', false);
    }

    public function report(Throwable $exception, array $context = []): void
    {
        $message = $exception->getMessage();
        $this->logger->error($message, ['file' => $exception->getFile(), 'line' => $exception->getLine(), 'context' => $context]);

        if (!$this->enabled || $this->dsn === '') {
            return;
        }

        $payload = [
            'message' => $message,
            'level' => 'error',
            'exception' => [
                'type' => get_debug_type($exception),
                'value' => $message,
                'stacktrace' => $exception->getTraceAsString(),
            ],
            'extra' => $context,
        ];

        $ch = curl_init($this->dsn);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
