<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class InputDetectionService
{
    /**
     * Detect if the input is an email or phone number.
     *
     * @param string $input
     * @return string|null 'email', 'phone', or null if invalid
     */
    public function detectType(string $input): ?string
    {
        if ($this->isValidEmail($input)) {
            return 'email';
        }

        if ($this->isValidPhone($input)) {
            return 'phone';
        }

        return null;
    }

    /**
     * Validate email format (RFC 5322 compliant via Laravel's validator).
     *
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        return !$validator->fails();
    }

    /**
     * Validate phone number format (E.164 standard).
     *
     * @param string $phone
     * @return bool
     */
    public function isValidPhone(string $phone): bool
    {
        // E.164 regex: ^\+[1-9]\d{1,14}$
        // allowing input without + if we want to auto-format, but strictly:
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
    }

    /**
     * Format phone number to E.164 if possible.
     *
     * @param string $phone
     * @return string|null
     */
    public function formatPhone(string $phone): ?string
    {
        // Remove non-digit characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        // Basic E.164 check
        if (preg_match('/^\+[1-9]\d{1,14}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }
}
