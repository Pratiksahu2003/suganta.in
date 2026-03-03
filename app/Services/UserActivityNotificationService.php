<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class UserActivityNotificationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // ========================================
    // PROFILE ACTIVITIES
    // ========================================

    /**
     * Profile updated notification
     */
    public function profileUpdated(User $user, array $changes = []): void
    {
        $changeMessages = [];
        foreach ($changes as $field => $value) {
            switch ($field) {
                case 'name':
                    $changeMessages[] = "name updated to '{$value}'";
                    break;
                case 'email':
                    $changeMessages[] = "email updated to '{$value}'";
                    break;
                case 'phone':
                    $changeMessages[] = "phone number updated to '{$value}'";
                    break;
                case 'avatar':
                    $changeMessages[] = "profile picture updated";
                    break;
                case 'bio':
                    $changeMessages[] = "bio updated";
                    break;
                case 'location':
                    $changeMessages[] = "location updated to '{$value}'";
                    break;
                case 'timezone':
                    $changeMessages[] = "timezone updated to '{$value}'";
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
                'changes' => $changes,
                'updated_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('dashboard.profile'),
            'low'
        );
    }

    /**
     * Password changed notification
     */
    public function passwordChanged(User $user, array $additionalInfo = []): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Password Changed',
            'Your password has been successfully changed. If you did not make this change, please contact support immediately.',
            'security',
            array_merge([
                'user_id' => $user->id,
                'changed_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'location' => $this->getLocationFromIP(Request::ip())
            ], $additionalInfo),
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * Password reset notification
     */
    public function passwordReset(User $user, array $additionalInfo = []): void
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

    /**
     * Account verification notification
     */
    public function accountVerified(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Verified',
            'Your account has been successfully verified. You now have full access to all features.',
            'account',
            [
                'user_id' => $user->id,
                'verified_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('dashboard'),
            'normal'
        );
    }

    // ========================================
    // LOGIN ACTIVITIES
    // ========================================

    /**
     * Successful login notification
     */
    public function loginSuccessful(User $user, array $additionalInfo = []): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Login Successful',
            'You have successfully logged into your account.',
            'login',
            array_merge([
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'location' => $this->getLocationFromIP(Request::ip()),
                'device_type' => $this->getDeviceType(Request::userAgent())
            ], $additionalInfo),
            route('dashboard'),
            'low'
        );
    }

    /**
     * Failed login attempt notification
     */
    public function loginFailed(User $user, string $reason = 'Invalid credentials'): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Login Failed',
            "Failed login attempt: {$reason}",
            'security',
            [
                'user_id' => $user->id,
                'failed_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'location' => $this->getLocationFromIP(Request::ip()),
                'reason' => $reason
            ],
            route('login'),
            'high'
        );
    }

    /**
     * Unusual login location notification
     */
    public function unusualLoginLocation(User $user, string $location): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Unusual Login Location',
            "New login detected from {$location}. If this wasn't you, please secure your account immediately.",
            'security',
            [
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'location' => $location,
                'device_type' => $this->getDeviceType(Request::userAgent())
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * New device login notification
     */
    public function newDeviceLogin(User $user, string $deviceInfo): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'New Device Login',
            "New device login detected: {$deviceInfo}",
            'security',
            [
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'device_info' => $deviceInfo,
                'location' => $this->getLocationFromIP(Request::ip())
            ],
            route('dashboard.profile'),
            'normal'
        );
    }

    // ========================================
    // LOGOUT ACTIVITIES
    // ========================================

    /**
     * Logout notification
     */
    public function logout(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Logout Successful',
            'You have been successfully logged out of your account.',
            'login',
            [
                'user_id' => $user->id,
                'logout_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'session_duration' => $this->getSessionDuration()
            ],
            route('login'),
            'low'
        );
    }

    // ========================================
    // SECURITY ACTIVITIES
    // ========================================

    /**
     * Two-factor authentication enabled
     */
    public function twoFactorEnabled(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Two-Factor Authentication Enabled',
            'Two-factor authentication has been enabled for your account.',
            'security',
            [
                'user_id' => $user->id,
                'enabled_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('dashboard.profile'),
            'normal'
        );
    }

    /**
     * Two-factor authentication disabled
     */
    public function twoFactorDisabled(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Two-Factor Authentication Disabled',
            'Two-factor authentication has been disabled for your account.',
            'security',
            [
                'user_id' => $user->id,
                'disabled_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * Account locked notification
     */
    public function accountLocked(User $user, string $reason = 'Multiple failed login attempts'): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Locked',
            "Your account has been locked: {$reason}",
            'security',
            [
                'user_id' => $user->id,
                'locked_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'reason' => $reason
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * Account unlocked notification
     */
    public function accountUnlocked(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Account Unlocked',
            'Your account has been unlocked. You can now log in normally.',
            'security',
            [
                'user_id' => $user->id,
                'unlocked_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('login'),
            'normal'
        );
    }

    // ========================================
    // EMAIL ACTIVITIES
    // ========================================

    /**
     * Email changed notification
     */
    public function emailChanged(User $user, string $oldEmail, string $newEmail): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Email Address Changed',
            "Your email address has been changed from {$oldEmail} to {$newEmail}",
            'account',
            [
                'user_id' => $user->id,
                'changed_at' => now(),
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent()
            ],
            route('dashboard.profile'),
            'high'
        );
    }

    /**
     * Email verification sent
     */
    public function emailVerificationSent(User $user): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Email Verification Sent',
            'A verification email has been sent to your email address.',
            'account',
            [
                'user_id' => $user->id,
                'sent_at' => now(),
                'email' => $user->email
            ],
            route('verification.notice'),
            'normal'
        );
    }

    // ========================================
    // PREFERENCES ACTIVITIES
    // ========================================

    /**
     * Notification preferences updated
     */
    public function notificationPreferencesUpdated(User $user, array $changes = []): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Notification Preferences Updated',
            'Your notification preferences have been updated.',
            'preferences',
            [
                'user_id' => $user->id,
                'updated_at' => now(),
                'changes' => $changes
            ],
            route('notifications.settings'),
            'low'
        );
    }

    /**
     * Privacy settings updated
     */
    public function privacySettingsUpdated(User $user, array $changes = []): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Privacy Settings Updated',
            'Your privacy settings have been updated.',
            'preferences',
            [
                'user_id' => $user->id,
                'updated_at' => now(),
                'changes' => $changes
            ],
            route('dashboard.profile'),
            'normal'
        );
    }

    // ========================================
    // SUBSCRIPTION ACTIVITIES
    // ========================================

    /**
     * Subscription started
     */
    public function subscriptionStarted(User $user, string $planName, $amount): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Subscription Started',
            "Your {$planName} subscription has been activated. Amount: {$amount}",
            'subscription',
            [
                'user_id' => $user->id,
                'started_at' => now(),
                'plan_name' => $planName,
                'amount' => $amount
            ],
            route('subscription.details'),
            'normal'
        );
    }

    /**
     * Subscription cancelled
     */
    public function subscriptionCancelled(User $user, string $planName, $endDate): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Subscription Cancelled',
            "Your {$planName} subscription has been cancelled. Access until: {$endDate}",
            'subscription',
            [
                'user_id' => $user->id,
                'cancelled_at' => now(),
                'plan_name' => $planName,
                'end_date' => $endDate
            ],
            route('subscription.details'),
            'normal'
        );
    }

    /**
     * Subscription renewed
     */
    public function subscriptionRenewed(User $user, string $planName, $amount): void
    {
        $this->notificationService->createUserNotification(
            $user->id,
            'Subscription Renewed',
            "Your {$planName} subscription has been renewed. Amount: {$amount}",
            'subscription',
            [
                'user_id' => $user->id,
                'renewed_at' => now(),
                'plan_name' => $planName,
                'amount' => $amount
            ],
            route('subscription.details'),
            'normal'
        );
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get location from IP address (simplified)
     */
    private function getLocationFromIP(string $ip): string
    {
        // This is a simplified version. In production, you might want to use a service like MaxMind
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        // For demo purposes, return a generic location
        return 'Unknown Location';
    }

    /**
     * Get device type from user agent
     */
    private function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get session duration (simplified)
     */
    private function getSessionDuration(): string
    {
        // This would need to be implemented based on your session management
        return 'Unknown';
    }

    /**
     * Get user notification statistics
     */
    public function getUserActivityStats(User $user): array
    {
        return [
            'total_activities' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->count(),
            'security_activities' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereJsonContains('data->type', 'security')
                ->count(),
            'login_activities' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereJsonContains('data->type', 'login')
                ->count(),
            'profile_activities' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereJsonContains('data->type', 'profile')
                ->count(),
            'recent_activities' => Notification::where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    /**
     * Clean up old user activity notifications
     */
    public function cleanupOldUserActivities(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = Notification::where('created_at', '<', $cutoffDate)
            ->whereNotNull('read_at')
            ->whereJsonContains('data->type', ['login', 'profile', 'preferences'])
            ->delete();

        Log::info("Cleaned up {$deletedCount} old user activity notifications older than {$days} days");
        
        return $deletedCount;
    }
} 
