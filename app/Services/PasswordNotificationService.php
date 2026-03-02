<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordActivityNotification;
use Illuminate\Support\Facades\Log;

class PasswordNotificationService
{
    /**
     * Send notification when user updates password from profile
     */
    public function passwordUpdated(User $user, array $additionalInfo = []): void
    {
        try {
            $user->notify(new PasswordActivityNotification(
                $user,
                'password_updated',
                array_merge($additionalInfo, [
                    'updated_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'source' => 'profile_update'
                ])
            ));

            Log::info('Password updated notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'additional_info' => $additionalInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password updated notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }
    }

    /**
     * Send notification when user resets password via forgot password flow
     */
    public function passwordReset(User $user, array $additionalInfo = []): void
    {
        try {
            $user->notify(new PasswordActivityNotification(
                $user,
                'password_reset',
                array_merge($additionalInfo, [
                    'reset_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'source' => 'forgot_password_reset'
                ])
            ));

            Log::info('Password reset notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'additional_info' => $additionalInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }
    }

    /**
     * Send notification when user changes password from any source
     */
    public function passwordChanged(User $user, array $additionalInfo = []): void
    {
        try {
            $user->notify(new PasswordActivityNotification(
                $user,
                'password_changed',
                array_merge($additionalInfo, [
                    'changed_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'source' => $additionalInfo['source'] ?? 'unknown'
                ])
            ));

            Log::info('Password changed notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'additional_info' => $additionalInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password changed notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }
    }

    /**
     * Send notification for any password-related activity
     */
    public function sendPasswordNotification(User $user, string $activityType, array $additionalInfo = []): void
    {
        try {
            $user->notify(new PasswordActivityNotification(
                $user,
                $activityType,
                array_merge($additionalInfo, [
                    'timestamp' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ])
            ));

            Log::info("Password {$activityType} notification sent", [
                'user_id' => $user->id,
                'email' => $user->email,
                'activity_type' => $activityType,
                'additional_info' => $additionalInfo
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send password {$activityType} notification: " . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'activity_type' => $activityType
            ]);
        }
    }
}
