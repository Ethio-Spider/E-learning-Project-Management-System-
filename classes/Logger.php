<?php
/**
 * Logger - Handles application logging
 */

declare(strict_types=1);

class Logger
{
    private const LOG_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
    ];
    
    private string $logDir;
    private string $logLevel;
    
    public function __construct(string $logDir, string $logLevel = 'INFO')
    {
        $this->logDir = $logDir;
        $this->logLevel = $logLevel;
        
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Log a message
     */
    public function log(string $message, string $level = 'INFO', array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' | Context: ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
        
        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log($message, 'DEBUG', $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log($message, 'INFO', $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log($message, 'WARNING', $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log($message, 'ERROR', $context);
    }
    
    private function shouldLog(string $level): bool
    {
        return (self::LOG_LEVELS[$level] ?? 999) >= (self::LOG_LEVELS[$this->logLevel] ?? 1);
    }
}
