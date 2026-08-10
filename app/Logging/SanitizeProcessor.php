<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class SanitizeProcessor implements ProcessorInterface
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password',
        'token', 'access_token', 'refresh_token', 'api_key',
        'authorization', 'secret', 'card_number', 'cvv', 'pin',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->sanitize($record->context);
        $extra = $this->sanitize($record->extra);

        return $record->with(context: $context, extra: $extra);
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}
