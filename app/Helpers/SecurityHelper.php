<?php

declare(strict_types=1);

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitize HTML input to prevent XSS attacks.
     *
     * @param string|null $input
     * @param bool $allowBasicHtml
     * @return string
     */
    public static function sanitizeHtml(?string $input, bool $allowBasicHtml = false): string
    {
        if ($input === null) {
            return '';
        }

        if ($allowBasicHtml) {
            // Permette solo tag HTML sicuri come <p>, <br>, <strong>, <em>
            $allowedTags = '<p><br><strong><em><u><ol><ul><li>';
            return strip_tags($input, $allowedTags);
        }

        // Rimuove tutti i tag HTML
        return strip_tags($input);
    }

    /**
     * Escape output for safe HTML display.
     *
     * @param string|null $output
     * @return string
     */
    public static function escapeHtml(?string $output): string
    {
        if ($output === null) {
            return '';
        }

        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize filename to prevent directory traversal attacks.
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Rimuove caratteri pericolosi
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Rimuove .. per prevenire directory traversal
        $filename = str_replace('..', '', $filename);
        
        // Limita lunghezza
        return substr($filename, 0, 255);
    }

    /**
     * Validate and sanitize URL.
     *
     * @param string|null $url
     * @param array $allowedSchemes
     * @return string|null
     */
    public static function sanitizeUrl(?string $url, array $allowedSchemes = ['http', 'https']): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Check allowed schemes
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], $allowedSchemes, true)) {
            return null;
        }

        return $url;
    }

    /**
     * Sanitize email address.
     *
     * @param string|null $email
     * @return string|null
     */
    public static function sanitizeEmail(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return strtolower($email);
    }

    /**
     * Generate CSRF token hash for additional validation.
     *
     * @param string $token
     * @param string $userAgent
     * @param string $ipAddress
     * @return string
     */
    public static function generateCsrfHash(string $token, string $userAgent, string $ipAddress): string
    {
        return hash_hmac('sha256', $token . $userAgent . $ipAddress, config('app.key'));
    }

    /**
     * Mask sensitive data for logging (e.g., email, phone).
     *
     * @param string|null $data
     * @param int $visibleChars
     * @return string
     */
    public static function maskSensitiveData(?string $data, int $visibleChars = 3): string
    {
        if ($data === null || $data === '') {
            return '';
        }

        $length = strlen($data);
        
        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }

        $visible = substr($data, 0, $visibleChars);
        $masked = str_repeat('*', $length - $visibleChars);

        return $visible . $masked;
    }

    /**
     * Check if IP address is in allowed range (CIDR notation).
     *
     * @param string $ip
     * @param array $allowedRanges
     * @return bool
     */
    public static function isIpAllowed(string $ip, array $allowedRanges): bool
    {
        foreach ($allowedRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }

        [$subnet, $mask] = explode('/', $range);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}

