<?php
/**
 * PaymentService - Handles payment processing with Stripe and PayPal
 */

declare(strict_types=1);

class PaymentService
{
    private string $stripePublicKey;
    private string $stripeSecretKey;
    private string $paypalClientId;
    private string $paypalSecret;
    private PDO $pdo;
    
    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->stripePublicKey = $config['stripe_public_key'] ?? $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
        $this->stripeSecretKey = $config['stripe_secret_key'] ?? $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $this->paypalClientId = $config['paypal_client_id'] ?? $_ENV['PAYPAL_CLIENT_ID'] ?? '';
        $this->paypalSecret = $config['paypal_secret'] ?? $_ENV['PAYPAL_SECRET'] ?? '';
    }

    private function getUserEmailById(int $userId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row['email'] ?? null;
    }

    private function getCourseTitle(int $courseId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT title FROM projects WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$courseId]);
        $row = $stmt->fetch();

        return $row['title'] ?? null;
    }
    
    /**
     * Initiate payment
     */
    public function initiatePayment(int $userId, int $courseId, float $amount, string $paymentMethod = 'stripe'): array
    {
        $paymentId = $this->generatePaymentId();
        $studentEmail = $this->getUserEmailById($userId) ?? 'unknown@example.com';
        $courseTitle = $this->getCourseTitle($courseId) ?? 'Course';

        $sql = 'INSERT INTO payments (enrollment_id, student_email, course_id, course_title, amount, currency, payment_method, transaction_id, status, payment_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([0, $studentEmail, $courseId, $courseTitle, $amount, 'USD', $paymentMethod, $paymentId, 'Pending']);
        
        $localId = (int)$this->pdo->lastInsertId();
        
        if ($paymentMethod === 'stripe') {
            return $this->createStripeSession($localId, $paymentId, $amount, $courseId);
        } elseif ($paymentMethod === 'paypal') {
            return $this->createPayPalOrder($localId, $paymentId, $amount, $courseId);
        }
        
        return ['success' => false, 'message' => 'Invalid payment method'];
    }
    
    /**
     * Create Stripe payment session
     */
    private function createStripeSession(int $localPaymentId, string $paymentId, float $amount, int $courseId): array
    {
        if (empty($this->stripeSecretKey)) {
            return ['success' => false, 'message' => 'Stripe not configured'];
        }
        
        // In production, use Stripe PHP SDK
        // For now, return a mock response
        
        return [
            'success' => true,
            'method' => 'stripe',
            'payment_id' => $paymentId,
            'local_id' => $localPaymentId,
            'session_url' => 'https://checkout.stripe.com/session/' . $paymentId,
            'amount' => $amount,
        ];
    }
    
    /**
     * Create PayPal order
     */
    private function createPayPalOrder(int $localPaymentId, string $paymentId, float $amount, int $courseId): array
    {
        if (empty($this->paypalClientId)) {
            return ['success' => false, 'message' => 'PayPal not configured'];
        }
        
        // In production, use PayPal SDK
        // For now, return a mock response
        
        return [
            'success' => true,
            'method' => 'paypal',
            'payment_id' => $paymentId,
            'local_id' => $localPaymentId,
            'order_id' => 'PAYPAL-' . $paymentId,
            'amount' => $amount,
        ];
    }
    
    /**
     * Confirm payment
     */
    public function confirmPayment(string $paymentId, string $transactionId): bool
    {
        $sql = 'UPDATE payments SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE transaction_id = ? OR payment_id = ?';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(['Completed', $transactionId, $paymentId, $paymentId]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE transaction_id = ? OR payment_id = ? LIMIT 1');
        $stmt->execute([$paymentId, $paymentId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Cancel payment
     */
    public function cancelPayment(string $paymentId, string $reason = ''): bool
    {
        $sql = 'UPDATE payments SET status = ?, refund_reason = ? WHERE transaction_id = ? OR payment_id = ?';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(['Cancelled', $reason, $paymentId, $paymentId]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Process refund
     */
    public function processRefund(string $paymentId, string $reason = ''): bool
    {
        $sql = 'UPDATE payments SET status = ?, refund_date = CURRENT_TIMESTAMP, refund_reason = ? 
                WHERE (transaction_id = ? OR payment_id = ?) AND status = ?';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(['Refunded', $reason, $paymentId, $paymentId, 'Completed']);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Get user payment history
     */
    public function getUserPayments(int $userId, int $limit = 50): array
    {
        $userEmail = $this->getUserEmailById($userId);
        if ($userEmail === null) {
            return [];
        }

        $sql = 'SELECT p.*, c.title as course_title 
                FROM payments p 
                JOIN projects c ON p.course_id = c.id 
                WHERE p.student_email = ?
                ORDER BY p.created_at DESC 
                LIMIT ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userEmail, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get course revenue
     */
    public function getCourseRevenue(int $courseId): array
    {
        $sql = 'SELECT 
                COUNT(*) as total_purchases,
                SUM(CASE WHEN status = "Completed" THEN amount ELSE 0 END) as total_revenue,
                COUNT(CASE WHEN status = "Completed" THEN 1 END) as completed_payments,
                COUNT(CASE WHEN status = "Refunded" THEN 1 END) as refunded_count
                FROM payments 
                WHERE course_id = ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Generate unique payment ID
     */
    private function generatePaymentId(): string
    {
        return 'PAY-' . time() . '-' . bin2hex(random_bytes(4));
    }
    
    /**
     * Validate payment amount
     */
    public function validateAmount(float $amount): bool
    {
        return $amount > 0 && $amount <= 999999.99;
    }
}
