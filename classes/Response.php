<?php
/**
 * Response - Standardized API responses
 */

declare(strict_types=1);

class Response
{
    private bool $success;
    private string $message;
    private mixed $data;
    private int $statusCode;
    
    public function __construct(bool $success, string $message = '', mixed $data = null, int $statusCode = 200)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }
    
    /**
     * Send response and exit
     */
    public function send(): never
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($this->statusCode);
        
        $response = [
            'success' => $this->success,
            'message' => $this->message,
        ];
        
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Create success response
     */
    public static function success(string $message = '', mixed $data = null, int $statusCode = 200): self
    {
        return new self(true, $message, $data, $statusCode);
    }
    
    /**
     * Create error response
     */
    public static function error(string $message = '', mixed $data = null, int $statusCode = 400): self
    {
        return new self(false, $message, $data, $statusCode);
    }
    
    /**
     * Create created response (201)
     */
    public static function created(string $message = '', mixed $data = null): self
    {
        return new self(true, $message, $data, 201);
    }
    
    /**
     * Create not found response (404)
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self(false, $message, null, 404);
    }
    
    /**
     * Create validation error response (422)
     */
    public static function validationError(string $message = 'Validation failed', array $errors = []): self
    {
        return new self(false, $message, ['errors' => $errors], 422);
    }
    
    /**
     * Create conflict response (409)
     */
    public static function conflict(string $message = 'Resource already exists'): self
    {
        return new self(false, $message, null, 409);
    }
    
    /**
     * Create unauthorized response (401)
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(false, $message, null, 401);
    }
    
    /**
     * Create forbidden response (403)
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(false, $message, null, 403);
    }
    
    /**
     * Create server error response (500)
     */
    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self(false, $message, null, 500);
    }
}
