<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createUserNotification(
        int $userId,
        string $title,
        string $message,
        string $type = 'general',
        array $data = [],
        ?string $actionUrl = null,
        string $priority = 'normal'
    ): Notification {
        return Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\SystemNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => array_merge($data, [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'action_url' => $actionUrl,
                'priority' => $priority,
            ]),
            'read_at' => null,
        ]);
    }

    /**
     * Create notifications for all users in one or more roles.
     *
     * Note: Users can have a primary `role` column and/or many-to-many `roles()`.
     *
     * @return array<int, Notification> Created notifications
     */
    public function createRoleNotification(
        string|array $roles,
        string $title,
        string $message,
        string $type = 'general',
        array $data = [],
        ?string $actionUrl = null,
        string $priority = 'normal'
    ): array {
        $roles = is_array($roles) ? array_values(array_filter($roles)) : [$roles];
        $roles = array_values(array_unique(array_map('strval', $roles)));

        if (count($roles) === 0) {
            return [];
        }

        $userIds = User::query()
            ->whereIn('role', $roles)
            ->orWhereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            })
            ->pluck('id');

        $created = [];
        foreach ($userIds as $userId) {
            $created[] = $this->createUserNotification(
                (int) $userId,
                $title,
                $message,
                $type,
                $data,
                $actionUrl,
                $priority
            );
        }

        return $created;
    }
}
