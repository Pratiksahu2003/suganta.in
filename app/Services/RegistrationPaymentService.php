<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegistrationPaymentService
{
    public function __construct(
        protected CashfreeService $cashfree
    ) {}

    /**
     * Get or create a registration payment and return the checkout URL.
     * Returns an array with keys: success, checkout_url?, already_paid?, message?, order_id?
     *
     * @param  User  $user
     * @param  string  $source  'web' or 'api'
     * @return array{success: bool, checkout_url?: string, already_paid?: bool, message?: string, order_id?: string}
     */
    public function getOrCreateCheckoutUrl(User $user, string $source = 'web'): array
    {
        // Already completed
        if (($user->registration_fee_status ?? null) === 'paid' || ($user->registration_fee_status ?? null) === 'not_required') {
            return ['success' => true, 'already_paid' => true];
        }

        // Normalize missing status for older users
        if (($user->registration_fee_status ?? null) === 'not_required' || empty($user->registration_fee_status)) {
            $user->update(['registration_fee_status' => 'pending']);
        }

        if (!$user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => 'Please verify your email first before making payment.',
            ];
        }

        $registrationConfig = config('registration', []);
        $charges = data_get($registrationConfig, 'charges', []);
        $roleCharges = is_array($charges) ? ($charges[$user->role] ?? null) : null;

        if (!$roleCharges) {
            Log::channel('payment')->error('Registration charges not found for role', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);
            return [
                'success' => false,
                'message' => 'Registration charges not configured for your role. Please contact support.',
            ];
        }

        $requiredRoles = data_get($registrationConfig, 'payment.required_for_roles', []);
        $requiresPayment = is_array($requiredRoles) && in_array($user->role, $requiredRoles, true);

        if (!$requiresPayment) {
            $user->update([
                'verification_status' => 'verified',
                'is_active' => true,
                'registration_fee_status' => 'not_required',
            ]);
            return ['success' => true, 'already_paid' => true];
        }

        if (empty(config('cashfree.app_id')) || empty(config('cashfree.secret_key'))) {
            Log::channel('payment')->error('Registration payment initialization failed - Cashfree not configured', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);
            return [
                'success' => false,
                'message' => 'Payment system is not configured. Please contact administrator.',
            ];
        }

        // Reuse existing pending payment if any
        $existingPayment = Payment::where('user_id', $user->id)
            ->where('meta->type', 'registration')
            ->whereIn('status', ['created', 'pending'])
            ->first();

        if ($existingPayment) {
            try {
                $orderResponse = $existingPayment->gateway_response ?? [];
                $checkoutUrl = $this->cashfree->getCheckoutUrl($orderResponse);
                if ($checkoutUrl) {
                    return [
                        'success' => true,
                        'checkout_url' => $checkoutUrl,
                        'order_id' => $existingPayment->order_id,
                        'actual_price' => $roleCharges['actual_price'],
                        'discounted_price' => $roleCharges['discounted_price'],
                        'description' => $roleCharges['description'],
                    ];
                }
            } catch (\Exception $e) {
                Log::channel('payment')->warning('Existing registration payment missing session, trying refresh', [
                    'payment_id' => $existingPayment->id,
                    'order_id' => $existingPayment->order_id,
                    'error' => $e->getMessage(),
                ]);
                try {
                    $freshOrder = $this->cashfree->getOrder($existingPayment->order_id);
                    $existingPayment->update(['gateway_response' => $freshOrder]);
                    $checkoutUrl = $this->cashfree->getCheckoutUrl($freshOrder);
                    if ($checkoutUrl) {
                        return [
                            'success' => true,
                            'checkout_url' => $checkoutUrl,
                            'order_id' => $existingPayment->order_id,
                        ];
                    }
                } catch (\Exception $inner) {
                    Log::channel('payment')->error('Failed to refresh registration payment order', [
                        'payment_id' => $existingPayment->id,
                        'order_id' => $existingPayment->order_id,
                        'error' => $inner->getMessage(),
                    ]);
                    $existingPayment->update(['status' => 'failed']);
                }
            }
        }

        $amount = $roleCharges['discounted_price'];
        $orderId = 'REG_' . Str::upper(Str::random(10));

        $payment = Payment::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'currency' => $roleCharges['currency'] ?? 'INR',
            'amount' => $amount,
            'status' => 'created',
            'meta' => [
                'type' => 'registration',
                'role' => $user->role,
                'actual_price' => $roleCharges['actual_price'],
                'discounted_price' => $roleCharges['discounted_price'],
                'description' => $roleCharges['description'],
                'source' => $source,
            ],
        ]);

        try {
            $orderPayload = $this->cashfree->buildOrderPayload(
                $orderId,
                (string) $user->id,
                $user->email,
                $user->phone ?? '9999999999',
                (float) $amount,
                $roleCharges['currency'] ?? 'INR'
            );

            $orderResponse = $this->cashfree->createOrder($orderPayload);
            $checkoutUrl = $this->cashfree->getCheckoutUrl($orderResponse);

            $payment->update([
                'reference_id' => $orderResponse['cf_order_id'] ?? $orderId,
                'gateway_response' => $orderResponse,
                'status' => 'pending',
            ]);

            Log::channel('payment')->info('Registration payment initiated', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'role' => $user->role,
                'amount' => $amount,
                'source' => $source,
            ]);

            return [
                'success' => true,
                'checkout_url' => $checkoutUrl,
                'order_id' => $orderId,
                'actual_price' => $roleCharges['actual_price'],
                'discounted_price' => $roleCharges['discounted_price'],
                'description' => $roleCharges['description'],
            ];
        } catch (\Exception $e) {
            Log::channel('payment')->error('Registration payment initiation failed', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            $payment->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);

            return [
                'success' => false,
                'message' => 'Payment initialization failed. Please try again or contact support.',
            ];
        }
    }
}
