<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Preserve the raw request body for Cashfree webhook signature verification.
 *
 * Cashfree requires the EXACT raw payload bytes for HMAC verification.
 * Uses file_get_contents('php://input') when possible — per Cashfree docs,
 * this must be read BEFORE any framework parsing.
 *
 * @see https://www.cashfree.com/docs/payments/online/webhooks/signature-verification
 */
class PreserveRawBodyForWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/v1/payment/webhook')) {
            return $next($request);
        }

        // Read raw body BEFORE any consumption — php://input can only be read once.
        // If already exhausted (e.g. Symfony read it), fall back to getContent().
        $rawBody = file_get_contents('php://input');
        if ($rawBody === false || $rawBody === '') {
            $rawBody = $request->getContent();
        }

        $request->attributes->set('raw_body', $rawBody);

        return $next($request);
    }
}
