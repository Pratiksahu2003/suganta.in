<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Lead;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\StudyRequirement;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseApiController
{
    /**
     * Get dashboard summary for authenticated user.
     * Returns counts (support tickets, payments, leads, study requirements, posts),
     * latest X% of leads, 10 latest notifications, and user info.
     * Query param: latest_leads_percent (1-100, default 10) — percentage of leads to return.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Counts for auth user only
        $supportTicketsCount = SupportTicket::query()
            ->where('user_id', $user->id)
            ->count();

        $paymentsCount = Payment::query()
            ->where('user_id', $user->id)
            ->count();

        $leadsCount = Lead::query()
            ->forAuthUser($user->id)
            ->count();

        $studyRequirementsCount = StudyRequirement::query()
            ->where('user_id', $user->id)
            ->count();

        // Latest X% of leads for auth user
        $percent = (int) $request->get('latest_leads_percent', 10);
        $percent = max(1, min(100, $percent));
        $latestLeadsLimit = (int) max(1, ceil($leadsCount * $percent / 100));
        $latestLeads = Lead::query()
            ->forAuthUser($user->id)
            ->with(['user:id,name,email', 'leadOwner:id,name,email', 'assignedTo:id,name,email'])
            ->orderByDesc('created_at')
            ->limit($latestLeadsLimit)
            ->get()
            ->map(fn (Lead $l) => $l->toArray());

        // Last 5 payments for auth user
        $recentPayments = Payment::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Payment $p) => $this->formatPayment($p));

        // 10 latest notifications for auth user
        $notifications = Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (Notification $n) => $this->formatNotification($n));

        // User info: first_name, last_name, email, phone
        $userInfo = $this->formatUserInfo($user);

        return $this->success('Dashboard retrieved successfully.', [
            'counts' => [
                'support_tickets' => $supportTicketsCount,
                'payments' => $paymentsCount,
                'leads' => $leadsCount,
                'study_requirements' => $studyRequirementsCount,
            ],
            'latest_leads' => [
                'data' => $latestLeads,
                'meta' => [
                    'total' => $leadsCount,
                    'percent' => $percent,
                    'returned' => $latestLeads->count(),
                ],
            ],
            'recent_payments' => $recentPayments,
            'latest_notifications' => $notifications,
            'user' => $userInfo,
        ]);
    }

    /**
     * Format payment for dashboard response.
     */
    protected function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'currency' => $payment->currency,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'type' => $payment->meta['type'] ?? null,
            'description' => $payment->meta['description'] ?? null,
            'created_at' => $payment->created_at->toIso8601String(),
            'processed_at' => $payment->processed_at?->toIso8601String(),
        ];
    }

    /**
     * Format notification for response.
     */
    protected function formatNotification(Notification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'type' => $data['type'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }

    /**
     * Format user info: first_name, last_name, email, phone.
     */
    protected function formatUserInfo(User $user): array
    {
        $profile = $user->profile;

        return [
            'first_name' => $profile?->first_name ?? $this->parseFirstName($user->name),
            'last_name' => $profile?->last_name ?? $this->parseLastName($user->name),
            'email' => $user->email,
            'phone' => $user->phone ?? $profile?->phone_primary ?? null,
            'profile_pic' => $profile?->profile_image,
        ];
    }

    protected function parseFirstName(?string $name): string
    {
        if (!$name) {
            return '';
        }
        $parts = explode(' ', trim($name), 2);

        return $parts[0] ?? '';
    }

    protected function parseLastName(?string $name): ?string
    {
        if (!$name) {
            return null;
        }
        $parts = explode(' ', trim($name), 2);

        return $parts[1] ?? null;
    }
}
