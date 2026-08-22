<?php
/**
 * TwoFactorService - tiny TOTP implementation for 2FA verification.
 */

declare(strict_types=1);

class TwoFactorService
{
    public static function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; ++$i) {
            $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $secret;
    }

    public static function generateCode(string $secret, int $timeWindow = 30): string
    {
        $secretBits = self::base32Decode($secret);
        $counter = (int) floor(time() / $timeWindow);
        $counterBytes = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $counterBytes, $secretBits, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    public static function verifyCode(string $secret, string $code, int $timeWindow = 30, int $window = 1): bool
    {
        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return false;
        }

        for ($offset = -$window; $offset <= $window; ++$offset) {
            $time = (int) floor(time() / $timeWindow) + $offset;
            $counter = pack('N*', 0) . pack('N*', $time);
            $hash = hash_hmac('sha1', $counter, self::base32Decode($secret), true);
            $index = ord($hash[strlen($hash) - 1]) & 0x0F;
            $binary = ((ord($hash[$index]) & 0x7F) << 24)
                | ((ord($hash[$index + 1]) & 0xFF) << 16)
                | ((ord($hash[$index + 2]) & 0xFF) << 8)
                | (ord($hash[$index + 3]) & 0xFF);
            $otp = $binary % 1000000;
            if (str_pad((string) $otp, 6, '0', STR_PAD_LEFT) === $code) {
                return true;
            }
        }

        return false;
    }

    public static function getQrCodeUrl(string $issuer, string $email, string $secret): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $secret = rawurlencode($secret);
        return 'otpauth://totp/' . $label . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer);
    }

    private static function base32Decode(string $secret): string
    {
        $secret = strtoupper(str_replace(' ', '', trim($secret)));
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $values = array_flip(str_split($chars));
        $buffer = 0;
        $bits = 0;
        $output = '';

        foreach (str_split($secret) as $char) {
            if (!isset($values[$char])) {
                continue;
            }
            $buffer = ($buffer << 5) | $values[$char];
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }

    public static function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; ++$i) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }
}
