<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Exception;

class UserActivityNotificationService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function profileUpdated(User $user, array $changes = []): void
    {
        try {
            if (empty($changes)) {
                return;
            }

            $changeText = $this->buildProfileChangeMessage($changes);
            $activityData = $this->buildActivityData($user, $changes);

            $this->notificationService->createUserNotification(
                $user->id,
                'Profile Updated',
                "Your profile has been updated: {$changeText}",
                'profile',
                $activityData,
                route('dashboard.profile'),
                'low'
            );
        } catch (Exception $e) {
            Log::error('Failed to send profile updated notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function passwordChanged(User $user, array $additionalInfo = []): void
    {
        try {
            $securityData = $this->buildSecurityData($user, $additionalInfo);

            $this->notificationService->createUserNotification(
                $user->id,
                'Password Changed',
                'Your password has been successfully changed. If you did not make this change, please contact support immediately.',
                'security',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send password changed notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function passwordReset(User $user, array $additionalInfo = []): void
    {
        try {
            $securityData = $this->buildSecurityData($user, $additionalInfo);

            $this->notificationService->createUserNotification(
                $user->id,
                'Password Reset',
                'Your password has been reset successfully. If you did not request this reset, please contact support immediately.',
                'security',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send password reset notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function accountVerified(User $user): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Failed to send account verified notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function loginSuccessful(User $user, array $additionalInfo = []): void
    {
        try {
            $loginData = $this->buildLoginData($user, $additionalInfo);

            $this->notificationService->createUserNotification(
                $user->id,
                'Login Successful',
                'You have successfully logged into your account.',
                'login',
                $loginData,
                route('dashboard'),
                'low'
            );
        } catch (Exception $e) {
            Log::error('Failed to send login successful notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function loginFailed(User $user, string $reason = 'Invalid credentials'): void
    {
        try {
            $securityData = $this->buildSecurityData($user, ['reason' => $reason, 'failed_at' => now()]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Login Failed',
                "Failed login attempt: {$reason}",
                'security',
                $securityData,
                route('login'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send login failed notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function unusualLoginLocation(User $user, string $location): void
    {
        try {
            $securityData = $this->buildSecurityData($user, [
                'location' => $location,
                'device_type' => $this->getDeviceType(Request::userAgent())
            ]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Unusual Login Location',
                "New login detected from {$location}. If this wasn't you, please secure your account immediately.",
                'security',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send unusual login location notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function newDeviceLogin(User $user, string $deviceInfo): void
    {
        try {
            $securityData = $this->buildSecurityData($user, [
                'device_info' => $deviceInfo,
                'device_type' => $this->getDeviceType(Request::userAgent())
            ]);

            $this->notificationService->createUserNotification(
                $user->id,
                'New Device Login',
                "New device login detected: {$deviceInfo}",
                'security',
                $securityData,
                route('dashboard.profile'),
                'normal'
            );
        } catch (Exception $e) {
            Log::error('Failed to send new device login notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logout(User $user, ?string $sessionDuration = null): void
    {
        try {
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
                    'session_duration' => $sessionDuration ?? 'Unknown'
                ],
                route('login'),
                'low'
            );
        } catch (Exception $e) {
            Log::error('Failed to send logout notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function twoFactorEnabled(User $user): void
    {
        try {
            $securityData = $this->buildSecurityData($user, ['enabled_at' => now()]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Two-Factor Authentication Enabled',
                'Two-factor authentication has been enabled for your account.',
                'security',
                $securityData,
                route('dashboard.profile'),
                'normal'
            );
        } catch (Exception $e) {
            Log::error('Failed to send 2FA enabled notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function twoFactorDisabled(User $user): void
    {
        try {
            $securityData = $this->buildSecurityData($user, ['disabled_at' => now()]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Two-Factor Authentication Disabled',
                'Two-factor authentication has been disabled for your account.',
                'security',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send 2FA disabled notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function accountLocked(User $user, string $reason = 'Multiple failed login attempts'): void
    {
        try {
            $securityData = $this->buildSecurityData($user, [
                'locked_at' => now(),
                'reason' => $reason
            ]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Account Locked',
                "Your account has been locked: {$reason}",
                'security',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send account locked notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function accountUnlocked(User $user): void
    {
        try {
            $securityData = $this->buildSecurityData($user, ['unlocked_at' => now()]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Account Unlocked',
                'Your account has been unlocked. You can now log in normally.',
                'security',
                $securityData,
                route('login'),
                'normal'
            );
        } catch (Exception $e) {
            Log::error('Failed to send account unlocked notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function emailChanged(User $user, string $oldEmail, string $newEmail): void
    {
        try {
            $securityData = $this->buildSecurityData($user, [
                'changed_at' => now(),
                'old_email' => $oldEmail,
                'new_email' => $newEmail
            ]);

            $this->notificationService->createUserNotification(
                $user->id,
                'Email Address Changed',
                "Your email address has been changed from {$oldEmail} to {$newEmail}",
                'account',
                $securityData,
                route('dashboard.profile'),
                'high'
            );
        } catch (Exception $e) {
            Log::error('Failed to send email changed notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function emailVerificationSent(User $user): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Failed to send email verification sent notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function notificationPreferencesUpdated(User $user, array $changes = []): void
    {
        try {
            if (empty($changes)) {
                return;
            }

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
        } catch (Exception $e) {
            Log::error('Failed to send notification preferences updated notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function privacySettingsUpdated(User $user, array $changes = []): void
    {
        try {
            if (empty($changes)) {
                return;
            }

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
        } catch (Exception $e) {
            Log::error('Failed to send privacy settings updated notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function subscriptionStarted(User $user, string $planName, float $amount): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Failed to send subscription started notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function subscriptionCancelled(User $user, string $planName, string $endDate): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Failed to send subscription cancelled notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function subscriptionRenewed(User $user, string $planName, float $amount): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Failed to send subscription renewed notification', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function buildActivityData(User $user, array $additionalData = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()
        ], $additionalData);
    }

    protected function buildSecurityData(User $user, array $additionalData = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'location' => $this->getLocationFromIP(Request::ip()),
            'timestamp' => now()
        ], $additionalData);
    }

    protected function buildLoginData(User $user, array $additionalData = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'login_at' => now(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'location' => $this->getLocationFromIP(Request::ip()),
            'device_type' => $this->getDeviceType(Request::userAgent())
        ], $additionalData);
    }

    protected function buildProfileChangeMessage(array $changes): string
    {
        $changeMessages = [];
        foreach ($changes as $field => $value) {
            $changeMessages[] = match ($field) {
                'name' => "name updated to '{$value}'",
                'email' => "email updated to '{$value}'",
                'phone' => "phone number updated to '{$value}'",
                'avatar' => "profile picture updated",
                'bio' => "bio updated",
                'location' => "location updated to '{$value}'",
                'timezone' => "timezone updated to '{$value}'",
                default => "{$field} updated"
            };
        }

        return implode(', ', $changeMessages);
    }

    protected function getLocationFromIP(string $ip): string
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        return 'Unknown Location';
    }

    protected function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'Tablet';
        }
        
        return 'Desktop';
    }
} 
