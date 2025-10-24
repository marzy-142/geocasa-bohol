<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class InquiryLogFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new InquiryLineFormatter());
        }
    }
}

class InquiryLineFormatter extends LineFormatter
{
    /**
     * The format of a log record.
     */
    protected const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    /**
     * Format the log record.
     */
    public function format(LogRecord $record): string
    {
        $vars = parent::format($record);
        
        // Add structured data for inquiry logs
        if (isset($record->context['event'])) {
            $event = $record->context['event'];
            $inquiryId = $record->context['inquiry_id'] ?? 'N/A';
            $propertyId = $record->context['property_id'] ?? 'N/A';
            
            // Create structured log entry
            $structured = [
                'timestamp' => $record->datetime->format('Y-m-d H:i:s'),
                'event' => $event,
                'inquiry_id' => $inquiryId,
                'property_id' => $propertyId,
                'level' => $record->level->getName(),
                'message' => $record->message,
                'context' => $this->sanitizeContext($record->context)
            ];
            
            return json_encode($structured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        return $vars;
    }

    /**
     * Sanitize context data for logging
     */
    private function sanitizeContext(array $context): array
    {
        // Remove sensitive information
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($context[$key])) {
                $context[$key] = '[REDACTED]';
            }
        }
        
        // Mask email addresses partially
        if (isset($context['email'])) {
            $context['email'] = $this->maskEmail($context['email']);
        }
        
        // Mask IP addresses partially
        if (isset($context['ip_address'])) {
            $context['ip_address'] = $this->maskIpAddress($context['ip_address']);
        }
        
        return $context;
    }

    /**
     * Mask email address for privacy
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '[INVALID_EMAIL]';
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);
        }
        
        return $maskedUsername . '@' . $domain;
    }

    /**
     * Mask IP address for privacy
     */
    private function maskIpAddress(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.***.' . $parts[3];
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3)) . ':***:***:***:' . end($parts);
        }
        
        return '[INVALID_IP]';
    }
}