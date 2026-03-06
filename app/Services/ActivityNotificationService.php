<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use App\Models\Session;
use App\Models\SupportTicket;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Institute;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ActivityNotificationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // ========================================
    // SESSION RELATED NOTIFICATIONS
    // ========================================

    /**
     * Session created notification
     */
    public function sessionCreated(Session $session)
    {
        $teacher = $session->teacher;
        $student = $session->student;
        $institute = $session->institute;

        // Notify student
        $this->notificationService->createUserNotification(
            $student->user_id,
            'New Session Scheduled',
            "A new session '{$session->title}' has been scheduled with {$teacher->user->name} on " . $session->scheduled_at->format('M d, Y g:i A'),
            'session',
            [
                'session_id' => $session->id,
                'teacher_name' => $teacher->user->name,
                'session_title' => $session->title,
                'scheduled_at' => $session->scheduled_at
            ],
            route('sessions.show', $session->id),
            'normal'
        );

        // Notify teacher
        $this->notificationService->createUserNotification(
            $teacher->user_id,
            'New Session Assignment',
            "You have been assigned a new session '{$session->title}' with {$student->user->name} on " . $session->scheduled_at->format('M d, Y g:i A'),
            'session',
            [
                'session_id' => $session->id,
                'student_name' => $student->user->name,
                'session_title' => $session->title,
                'scheduled_at' => $session->scheduled_at
            ],
            route('sessions.show', $session->id),
            'normal'
        );

        // Notify institute admin if different from teacher
        if ($institute && $institute->user_id !== $teacher->user_id) {
            $this->notificationService->createUserNotification(
                $institute->user_id,
                'New Session Created',
                "A new session '{$session->title}' has been created in your institute",
                'session',
                [
                    'session_id' => $session->id,
                    'teacher_name' => $teacher->user->name,
                    'student_name' => $student->user->name,
                    'session_title' => $session->title
                ],
                route('sessions.show', $session->id),
                'normal'
            );
        }
    }

    /**
     * Session updated notification
     */
    public function sessionUpdated(Session $session, $changes = [])
    {
        $teacher = $session->teacher;
        $student = $session->student;

        $changeMessages = [];
        foreach ($changes as $field => $value) {
            switch ($field) {
                case 'title':
                    $changeMessages[] = "title changed to '{$value}'";
                    break;
                case 'scheduled_at':
                    $changeMessages[] = "time changed to " . Carbon::parse($value)->format('M d, Y g:i A');
                    break;
                case 'status':
                    $changeMessages[] = "status changed to {$value}";
                    break;
            }
        }

        $changeText = implode(', ', $changeMessages);

        // Notify student
        $this->notificationService->createUserNotification(
            $student->user_id,
            'Session Updated',
            "Session '{$session->title}' has been updated: {$changeText}",
            'session',
            [
                'session_id' => $session->id,
                'session_title' => $session->title,
                'changes' => $changes
            ],
            route('sessions.show', $session->id),
            'normal'
        );

        // Notify teacher
        $this->notificationService->createUserNotification(
            $teacher->user_id,
            'Session Updated',
            "Session '{$session->title}' has been updated: {$changeText}",
            'session',
            [
                'session_id' => $session->id,
                'session_title' => $session->title,
                'changes' => $changes
            ],
            route('sessions.show', $session->id),
            'normal'
        );
    }

    /**
     * Session cancelled notification
     */
    public function sessionCancelled(Session $session, $reason = null)
    {
        $teacher = $session->teacher;
        $student = $session->student;

        $reasonText = $reason ? " Reason: {$reason}" : '';

        // Notify student
        $this->notificationService->createUserNotification(
            $student->user_id,
            'Session Cancelled',
            "Session '{$session->title}' with {$teacher->user->name} has been cancelled.{$reasonText}",
            'session',
            [
                'session_id' => $session->id,
                'teacher_name' => $teacher->user->name,
                'session_title' => $session->title,
                'reason' => $reason
            ],
            route('sessions.show', $session->id),
            'high'
        );

        // Notify teacher
        $this->notificationService->createUserNotification(
            $teacher->user_id,
            'Session Cancelled',
            "Session '{$session->title}' with {$student->user->name} has been cancelled.{$reasonText}",
            'session',
            [
                'session_id' => $session->id,
                'student_name' => $student->user->name,
                'session_title' => $session->title,
                'reason' => $reason
            ],
            route('sessions.show', $session->id),
            'high'
        );
    }

    /**
     * Session reminder notification
     */
    public function sessionReminder(Session $session)
    {
        $teacher = $session->teacher;
        $student = $session->student;

        // Notify student
        $this->notificationService->createUserNotification(
            $student->user_id,
            'Session Reminder',
            "Reminder: You have a session '{$session->title}' with {$teacher->user->name} in 1 hour",
            'session',
            [
                'session_id' => $session->id,
                'teacher_name' => $teacher->user->name,
                'session_title' => $session->title,
                'scheduled_at' => $session->scheduled_at
            ],
            route('sessions.show', $session->id),
            'normal'
        );

        // Notify teacher
        $this->notificationService->createUserNotification(
            $teacher->user_id,
            'Session Reminder',
            "Reminder: You have a session '{$session->title}' with {$student->user->name} in 1 hour",
            'session',
            [
                'session_id' => $session->id,
                'student_name' => $student->user->name,
                'session_title' => $session->title,
                'scheduled_at' => $session->scheduled_at
            ],
            route('sessions.show', $session->id),
            'normal'
        );
    }

    // ========================================
    // SUPPORT TICKET NOTIFICATIONS
    // ========================================

    /**
     * Support ticket created notification
     */
    public function supportTicketCreated(SupportTicket $ticket)
    {
        $user = $ticket->user;

        // Notify user
        $this->notificationService->createUserNotification(
            $user->id,
            'Support Ticket Created',
            "Your support ticket '{$ticket->subject}' has been created successfully. Ticket ID: #{$ticket->id}",
            'support',
            [
                'ticket_id' => $ticket->id,
                'subject' => $ticket->subject,
                'priority' => $ticket->priority
            ],
            route('support-tickets.show', $ticket->id),
            'normal'
        );

        // Notify admins
        $this->notificationService->createRoleNotification(
            ['admin'],
            'New Support Ticket',
            "New support ticket '{$ticket->subject}' created by {$user->name}. Priority: {$ticket->priority}",
            'support',
            [
                'ticket_id' => $ticket->id,
                'user_name' => $user->name,
                'subject' => $ticket->subject,
                'priority' => $ticket->priority
            ],
            route('admin.support-tickets.show', $ticket->id),
            $ticket->priority === 'high' ? 'high' : 'normal'
        );
    }

    /**
     * Send notification when user account is verified
     */
    public function userVerified(User $user)
    {
        // Notify user
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Verified',
            "Congratulations! Your account has been verified successfully. You now have full access to all platform features.",
            'success',
            ['user_id' => $user->id, 'user_name' => $user->name, 'role' => $user->role],
            route('dashboard'),
            'normal'
        );
    }

    /**
     * Send notification when user account is rejected
     */
    public function userRejected(User $user, $reason = null)
    {
        // Notify user
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Verification Update',
            $reason ? "Your account verification was not approved. Reason: {$reason}" : "Your account verification was not approved. Please contact support for more information.",
            'warning',
            ['user_id' => $user->id, 'user_name' => $user->name, 'role' => $user->role, 'reason' => $reason],
            route('support-tickets.create'),
            'normal'
        );
    }

    /**
     * Support ticket updated notification
     */
    public function supportTicketUpdated(SupportTicket $ticket, $changes = [])
    {
        $user = $ticket->user;

        $changeMessages = [];
        foreach ($changes as $field => $value) {
            switch ($field) {
                case 'status':
                    $changeMessages[] = "status changed to {$value}";
                    break;
                case 'priority':
                    $changeMessages[] = "priority changed to {$value}";
                    break;
                case 'assigned_to':
                    $changeMessages[] = "assigned to " . User::find($value)->name;
                    break;
            }
        }

        $changeText = implode(', ', $changeMessages);

        // Notify user
        $this->notificationService->createUserNotification(
            $user->id,
            'Support Ticket Updated',
            "Your support ticket '{$ticket->subject}' has been updated: {$changeText}",
            'support',
            [
                'ticket_id' => $ticket->id,
                'subject' => $ticket->subject,
                'changes' => $changes
            ],
            route('support-tickets.show', $ticket->id),
            'normal'
        );
    }

    /**
     * Support ticket replied notification
     */
    public function supportTicketReplied(SupportTicket $ticket, $reply)
    {
        $user = $ticket->user;
        $replier = $reply->user;

        // Notify ticket owner
        $this->notificationService->createUserNotification(
            $user->id,
            'Support Ticket Reply',
            "You have received a reply on your support ticket '{$ticket->subject}' from {$replier->name}",
            'support',
            [
                'ticket_id' => $ticket->id,
                'subject' => $ticket->subject,
                'replier_name' => $replier->name,
                'reply_id' => $reply->id
            ],
            route('support-tickets.show', $ticket->id),
            'normal'
        );

        // Notify other participants (if any)
        if ($ticket->assigned_to && $ticket->assigned_to !== $replier->id) {
            $this->notificationService->createUserNotification(
                $ticket->assigned_to,
                'Support Ticket Reply',
                "A reply has been added to support ticket '{$ticket->subject}' by {$replier->name}",
                'support',
                [
                    'ticket_id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'replier_name' => $replier->name,
                    'reply_id' => $reply->id
                ],
                route('admin.support-tickets.show', $ticket->id),
                'normal'
            );
        }
    }

    // ========================================
    // MESSAGE NOTIFICATIONS
    // ========================================

    /**
     * New message notification
     */
    public function newMessage(Message $message)
    {
        $sender = $message->sender;
        $recipient = $message->recipient;

        // Notify recipient
        $this->notificationService->createUserNotification(
            $recipient->id,
            'New Message',
            "You have received a new message from {$sender->name}: " . substr($message->content, 0, 100) . (strlen($message->content) > 100 ? '...' : ''),
            'message',
            [
                'message_id' => $message->id,
                'sender_name' => $sender->name,
                'content_preview' => substr($message->content, 0, 100)
            ],
            route('messages.show', $message->id),
            'normal'
        );
    }

    // ========================================
    // PAYMENT NOTIFICATIONS
    // ========================================

    /**
     * Payment successful notification
     */
    public function paymentSuccessful(Payment $payment)
    {
        $user = $payment->user;

        $this->notificationService->createUserNotification(
            $user->id,
            'Payment Successful',
            "Your payment of {$payment->currency} {$payment->amount} has been processed successfully. Transaction ID: {$payment->transaction_id}",
            'payment',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'transaction_id' => $payment->transaction_id
            ],
            route('payments.show', $payment->id),
            'normal'
        );

        // Notify admins for large payments
        if ($payment->amount > 1000) {
            $this->notificationService->createRoleNotification(
                ['admin'],
                'Large Payment Received',
                "Large payment of {$payment->currency} {$payment->amount} received from {$user->name}",
                'payment',
                [
                    'payment_id' => $payment->id,
                    'user_name' => $user->name,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency
                ],
                route('admin.payments.show', $payment->id),
                'normal'
            );
        }
    }

    /**
     * Payment failed notification
     */
    public function paymentFailed(Payment $payment, $reason = null)
    {
        $user = $payment->user;

        $reasonText = $reason ? " Reason: {$reason}" : '';

        $this->notificationService->createUserNotification(
            $user->id,
            'Payment Failed',
            "Your payment of {$payment->currency} {$payment->amount} has failed.{$reasonText}",
            'payment',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reason' => $reason
            ],
            route('payments.show', $payment->id),
            'high'
        );
    }

    // ========================================
    // REVIEW NOTIFICATIONS
    // ========================================

    /**
     * New review notification
     */
    public function newReview(Review $review)
    {
        $reviewer = $review->user;
        $teacher = $review->teacher;

        // Notify teacher
        $this->notificationService->createUserNotification(
            $teacher->user_id,
            'New Review Received',
            "You have received a new {$review->rating}-star review from {$reviewer->name}",
            'review',
            [
                'review_id' => $review->id,
                'reviewer_name' => $reviewer->name,
                'rating' => $review->rating,
                'comment' => substr($review->comment, 0, 100)
            ],
            route('reviews.show', $review->id),
            'normal'
        );

        // Notify institute if different from teacher
        if ($teacher->institute && $teacher->institute->user_id !== $teacher->user_id) {
            $this->notificationService->createUserNotification(
                $teacher->institute->user_id,
                'New Teacher Review',
                "{$teacher->user->name} has received a new {$review->rating}-star review from {$reviewer->name}",
                'review',
                [
                    'review_id' => $review->id,
                    'teacher_name' => $teacher->user->name,
                    'reviewer_name' => $reviewer->name,
                    'rating' => $review->rating
                ],
                route('reviews.show', $review->id),
                'normal'
            );
        }
    }

    // ========================================
    // USER PROFILE NOTIFICATIONS
    // ========================================

    /**
     * Profile updated notification
     */
    public function profileUpdated(User $user, $changes = [])
    {
        $changeMessages = [];
        foreach ($changes as $field => $value) {
            switch ($field) {
                case 'name':
                    $changeMessages[] = "name updated";
                    break;
                case 'email':
                    $changeMessages[] = "email updated";
                    break;
                case 'phone':
                    $changeMessages[] = "phone number updated";
                    break;
                case 'avatar':
                    $changeMessages[] = "profile picture updated";
                    break;
            }
        }

        $changeText = implode(', ', $changeMessages);

        $this->notificationService->createUserNotification(
            $user->id,
            'Profile Updated',
            "Your profile has been updated: {$changeText}",
            'profile',
            [
                'user_id' => $user->id,
                'changes' => $changes
            ],
            route('dashboard.profile'),
            'low'
        );
    }

    /**
     * Account verification notification
     */
    public function accountVerified(User $user)
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Verified',
            'Your account has been successfully verified. You now have full access to all features.',
            'account',
            [
                'user_id' => $user->id,
                'verified_at' => now()
            ],
            route('dashboard'),
            'normal'
        );
    }

    /**
     * Password changed notification
     */
    public function passwordChanged(User $user)
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Password Changed',
            'Your password has been successfully changed. If you did not make this change, please contact support immediately.',
            'security',
            [
                'user_id' => $user->id,
                'changed_at' => now()
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * Password reset notification
     */
    public function passwordReset(User $user, $additionalInfo = [])
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Password Reset',
            'Your password has been reset successfully. If you did not request this reset, please contact support immediately.',
            'security',
            array_merge([
                'user_id' => $user->id,
                'reset_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'location' => $this->getLocationFromIP(Request::ip())
            ], $additionalInfo),
            route('dashboard.profile'),
            'high'
        );
    }

    // ========================================
    // INSTITUTE NOTIFICATIONS
    // ========================================

    /**
     * Teacher joined institute notification
     */
    public function teacherJoinedInstitute(TeacherProfile $teacher, Institute $institute)
    {
        // Notify institute admin
        $this->notificationService->createUserNotification(
            $institute->user_id,
            'New Teacher Joined',
            "{$teacher->user->name} has joined your institute as a teacher",
            'institute',
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->user->name,
                'institute_id' => $institute->id
            ],
            route('institute.teachers.show', $teacher->id),
            'normal'
        );

        // Notify admins
        $this->notificationService->createRoleNotification(
            ['admin'],
            'Teacher Joined Institute',
            "{$teacher->user->name} has joined {$institute->name} as a teacher",
            'institute',
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->user->name,
                'institute_id' => $institute->id,
                'institute_name' => $institute->name
            ],
            route('admin.users.show', $teacher->user_id),
            'normal'
        );
    }

    /**
     * Student enrolled notification
     */
    public function studentEnrolled(StudentProfile $student, Institute $institute)
    {
        // Notify institute admin
        $this->notificationService->createUserNotification(
            $institute->user_id,
            'New Student Enrolled',
            "{$student->user->name} has enrolled in your institute",
            'institute',
            [
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'institute_id' => $institute->id
            ],
            route('institute.students.show', $student->id),
            'normal'
        );

        // Notify student
        $this->notificationService->createUserNotification(
            $student->user_id,
            'Enrollment Successful',
            "You have successfully enrolled in {$institute->name}",
            'institute',
            [
                'student_id' => $student->id,
                'institute_id' => $institute->id,
                'institute_name' => $institute->name
            ],
            route('student.dashboard'),
            'normal'
        );
    }

    // ========================================
    // SYSTEM NOTIFICATIONS
    // ========================================

    /**
     * System maintenance notification
     */
    public function systemMaintenance($scheduledAt, $duration, $description = null)
    {
        $this->notificationService->createGlobalNotification(
            'System Maintenance Scheduled',
            "System maintenance is scheduled for " . Carbon::parse($scheduledAt)->format('M d, Y g:i A') . " for {$duration}. " . ($description ? $description : ''),
            'system',
            [
                'scheduled_at' => $scheduledAt,
                'duration' => $duration,
                'description' => $description
            ],
            route('maintenance.info'),
            'high'
        );
    }

    /**
     * New feature notification
     */
    public function newFeature($featureName, $description, $actionUrl = null)
    {
        $this->notificationService->createGlobalNotification(
            'New Feature Available',
            "New feature '{$featureName}' is now available! {$description}",
            'feature',
            [
                'feature_name' => $featureName,
                'description' => $description
            ],
            $actionUrl,
            'normal'
        );
    }

    /**
     * Security alert notification
     */
    public function securityAlert(User $user, $alertType, $description)
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Security Alert',
            "Security alert: {$description}",
            'security',
            [
                'user_id' => $user->id,
                'alert_type' => $alertType,
                'description' => $description
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    // ========================================
    // SCHEDULED NOTIFICATIONS
    // ========================================

    /**
     * Daily summary notification
     */
    public function dailySummary(User $user, $summary)
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Daily Summary',
            $summary,
            'summary',
            [
                'user_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'summary' => $summary
            ],
            route('dashboard'),
            'low'
        );
    }

    /**
     * Weekly report notification
     */
    public function weeklyReport(User $user, $report)
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Weekly Report',
            $report,
            'report',
            [
                'user_id' => $user->id,
                'week' => now()->format('Y-W'),
                'report' => $report
            ],
            route('reports.weekly'),
            'normal'
        );
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get notification statistics for a user
     */
    public function getUserStats(User $user)
    {
        return [
            'total' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->count(),
            'unread' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'read' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereNotNull('read_at')
                ->count(),
            'high_priority' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereJsonContains('data->priority', 'high')
                ->count(),
            'today' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereDate('created_at', today())
                ->count(),
            'this_week' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'this_month' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications($days = 90)
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = Notification::where('created_at', '<', $cutoffDate)
            ->whereNotNull('read_at')
            ->delete();

        Log::info("Cleaned up {$deletedCount} old notifications older than {$days} days");
        
        return $deletedCount;
    }

    /**
     * Get location from IP address (requires external service)
     */
    protected function getLocationFromIP($ip)
    {
        // This is a placeholder. In a real application, you would use a service
        // like IP2Location, MaxMind, or a similar geolocation API.
        // For demonstration, we'll return a placeholder.
        return "IP: {$ip}";
    }
} 