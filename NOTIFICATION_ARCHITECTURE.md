# Notification Architecture - API-Only Design

## Overview
This API uses a **resource-based notification system** that works seamlessly with both web and mobile app clients. Instead of hardcoded URLs, notifications include resource metadata that clients can use to navigate appropriately.

## Resource-Based Notification Structure

Each notification includes the following metadata in the `data` field:

```json
{
  "resource_type": "session|support_ticket|message|payment|review|profile|institute|dashboard|etc",
  "resource_id": 123,
  "action": "view|create|edit|security|info|alert"
}
```

### Benefits:
✅ **Platform Agnostic** - Works for web, iOS, Android, and any future clients  
✅ **Flexible Navigation** - Clients decide how to handle each resource type  
✅ **No Hardcoded URLs** - No dependency on web routes  
✅ **Future-Proof** - Easy to add new resource types  
✅ **Deep Linking** - Mobile apps can use resource_type + resource_id for deep links  

## Resource Types

| Resource Type | Description | Example Navigation |
|--------------|-------------|-------------------|
| `session` | Teacher sessions | `/sessions/{id}` or `app://sessions/{id}` |
| `support_ticket` | Support tickets | `/support-tickets/{id}` |
| `message` | Direct messages | `/messages/{id}` |
| `payment` | Payment transactions | `/payments/{id}` |
| `review` | Reviews and ratings | `/reviews/{id}` |
| `profile` | User profile | `/profile` or `/users/{id}` |
| `institute` | Institute details | `/institutes/{id}` |
| `dashboard` | Main dashboard | `/dashboard` |
| `subscription` | Subscription details | `/subscriptions` |
| `settings` | User settings | `/settings` |
| `auth` | Authentication related | N/A (info only) |
| `system` | System announcements | N/A (info only) |
| `feature` | New features | Custom URL in action_url |
| `report` | Reports and analytics | `/reports` |
| `email` | Email verification | `/email/verify` |

## Action Types

| Action | Description | Use Case |
|--------|-------------|----------|
| `view` | View the resource | Most common action |
| `create` | Create new resource | Redirect to creation form |
| `edit` | Edit existing resource | Redirect to edit form |
| `security` | Security-related action | Navigate to security settings |
| `info` | Informational only | No specific navigation |
| `alert` | Alert/warning | Show alert dialog |
| `verify` | Verification action | Navigate to verification screen |

## Client Implementation Examples

### Web Client (React/Vue/Angular)
```javascript
function handleNotificationClick(notification) {
  const { resource_type, resource_id, action } = notification.data;
  
  switch(resource_type) {
    case 'session':
      router.push(`/sessions/${resource_id}`);
      break;
    case 'support_ticket':
      router.push(`/support-tickets/${resource_id}`);
      break;
    case 'profile':
      router.push(action === 'security' ? '/profile/security' : '/profile');
      break;
    case 'dashboard':
      router.push('/dashboard');
      break;
    default:
      router.push('/notifications');
  }
}
```

### Mobile App (React Native/Flutter)
```javascript
function handleNotificationTap(notification) {
  const { resource_type, resource_id, action } = notification.data;
  
  switch(resource_type) {
    case 'session':
      navigation.navigate('SessionDetail', { id: resource_id });
      break;
    case 'support_ticket':
      navigation.navigate('TicketDetail', { id: resource_id });
      break;
    case 'message':
      navigation.navigate('MessageDetail', { id: resource_id });
      break;
    case 'profile':
      navigation.navigate(action === 'security' ? 'SecuritySettings' : 'Profile');
      break;
    default:
      navigation.navigate('Notifications');
  }
}
```

### Deep Linking (Mobile)
```
suganta://session/123
suganta://support-tickets/456
suganta://messages/789
suganta://profile
```

## API Response Format

### Notification Object
```json
{
  "id": "uuid",
  "type": "App\\Notifications\\SystemNotification",
  "notifiable_type": "App\\Models\\User",
  "notifiable_id": 123,
  "data": {
    "title": "New Session Created",
    "message": "Your session 'Math Basics' has been created successfully",
    "type": "session",
    "priority": "normal",
    "action_url": null,
    
    // Resource metadata
    "resource_type": "session",
    "resource_id": 456,
    "action": "view",
    
    // Additional context
    "session_id": 456,
    "session_title": "Math Basics",
    "session_type": "online",
    "scheduled_date": "2026-03-10",
    "scheduled_time": "14:00:00"
  },
  "read_at": null,
  "created_at": "2026-03-06T10:30:00.000000Z",
  "updated_at": "2026-03-06T10:30:00.000000Z"
}
```

## Best Practices

### For API Developers:
1. Always include `resource_type`, `resource_id`, and `action` in notification data
2. Set `action_url` to `null` unless you have a specific external URL
3. Include all relevant context in the `data` field
4. Use consistent resource type naming (snake_case)

### For Client Developers:
1. Always check for `resource_type` and `resource_id` before navigation
2. Handle unknown resource types gracefully (fallback to notifications list)
3. Use the `action` field to determine the specific screen/tab to show
4. Implement deep linking using the resource metadata

## Migration Notes

If you need to support legacy clients that expect URLs, you can add a middleware or transformer that generates URLs based on the resource metadata:

```php
// Example transformer
public function transformForLegacyClient($notification) {
    $data = $notification->data;
    
    if (!isset($data['action_url']) && isset($data['resource_type'])) {
        $data['action_url'] = $this->generateUrl(
            $data['resource_type'], 
            $data['resource_id'] ?? null
        );
    }
    
    return $data;
}
```

## Future Enhancements

1. **Push Notifications** - Use resource metadata for deep linking
2. **Email Notifications** - Generate web URLs from resource metadata
3. **SMS Notifications** - Include resource info in message
4. **Webhook Notifications** - Send resource metadata to external systems
5. **Analytics** - Track which resource types get the most engagement
