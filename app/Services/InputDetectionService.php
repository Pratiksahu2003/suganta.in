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
        // Consider phone valid if it can be formatted to E.164
        return $this->formatPhone($phone) !== null;
    }

    /**
     * Format phone number to E.164 if possible.
     *
     * @param string $phone
     * @return string|null
     */
    public function formatPhone(string $phone): ?string
    {
        // If already valid E.164, return as-is
        $trimmed = trim($phone);
        if (preg_match('/^\+[1-9]\d{1,14}$/', $trimmed)) {
            return $trimmed;
        }

        // Remove all non-digits to work with local/national formats
        $digits = preg_replace('/\D+/', '', $trimmed);
        if ($digits === null || $digits === '') {
            return null;
        }

        // Configurable defaults (tuned for e.g. IN by default)
        $countryCode = config('input_detection.phone.country_code', '+91');
        $nationalMin = (int) config('input_detection.phone.national_min_length', 10);
        $nationalMax = (int) config('input_detection.phone.national_max_length', 12);

        $countryDigits = ltrim($countryCode, '+');

        // Strip leading country code if user included it without '+'
        if (str_starts_with($digits, $countryDigits)) {
            $nationalNumber = substr($digits, strlen($countryDigits));
        } else {
            $nationalNumber = $digits;
        }

        $length = strlen($nationalNumber);
        if ($length < $nationalMin || $length > $nationalMax) {
            return null;
        }

        return '+' . $countryDigits . $nationalNumber;
    }
}
