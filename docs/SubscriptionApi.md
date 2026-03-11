# Subscription API

Endpoints for managing subscription plans and user subscriptions. Public routes expose plans; protected routes manage authenticated users' subscriptions and purchases.

**Base path**: `/api/v1`  
**Public**: Plan listing and details  
**Protected**: User subscriptions, purchase, cancel, renew (require `auth:sanctum` Bearer token)

---

## Endpoints Summary

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/subscriptions/plans` | List active subscription plans | Public |
| GET | `/subscriptions/plans/{plan}` | Get a specific plan by ID or slug | Public |
| GET | `/subscriptions/my-subscriptions` | Paginated list of user's subscriptions | Protected |
| GET | `/subscriptions/current` | User's current active subscription | Protected |
| POST | `/subscriptions/purchase` | Purchase a subscription plan | Protected |
| PATCH | `/subscriptions/{subscription}/cancel` | Cancel an active subscription | Protected |
| POST | `/subscriptions/{subscription}/renew` | Renew an expired/cancelled subscription | Protected |

---

## 1. List Plans

| | |
|---|---|
| **Endpoint** | `GET /api/v1/subscriptions/plans` |
| **Content-Type** | — |
| **Access** | Public (no auth required) |

Returns all active subscription plans, optionally filtered by subscription type. Plans are ordered by `sort_order` then `price`.

### Query Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| s_type | integer | No | 1 | — | Subscription type: `1` = Portfolio plans, `2` = Note download plans |

### Example Request

```
GET /api/v1/subscriptions/plans
GET /api/v1/subscriptions/plans?s_type=1
GET /api/v1/subscriptions/plans?s_type=2
```

### Success (200)

```json
{
  "message": "Subscription plans retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "plans": [
      {
        "id": 1,
        "name": "Basic Plan",
        "slug": "basic-plan",
        "description": "Basic subscription with limited features",
        "price": 99.00,
        "currency": "INR",
        "billing_period": "monthly",
        "max_images": 10,
        "max_files": 5,
        "features": ["Feature 1", "Feature 2"],
        "is_popular": false,
        "is_active": true,
        "sort_order": 1,
        "s_type": 1,
        "formatted_price": "INR 99.00",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

---

## 2. Get Plan

| | |
|---|---|
| **Endpoint** | `GET /api/v1/subscriptions/plans/{plan}` |
| **Content-Type** | — |
| **Access** | Public (no auth required) |

Returns details of a specific subscription plan. Uses plan ID in the URL. Returns 404 if the plan is inactive.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| plan | integer | **Yes** | Plan ID (e.g. `1`) |

### Query Parameters

None.

### Example Request

```
GET /api/v1/subscriptions/plans/1
```

### Success (200)

```json
{
  "message": "Subscription plan retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "plan": {
      "id": 1,
      "name": "Basic Plan",
      "slug": "basic-plan",
      "description": "Basic subscription with limited features",
      "price": 99.00,
      "currency": "INR",
      "billing_period": "monthly",
      "max_images": 10,
      "max_files": 5,
      "features": ["Feature 1", "Feature 2"],
      "is_popular": false,
      "is_active": true,
      "sort_order": 1,
      "s_type": 1,
      "formatted_price": "INR 99.00",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

### Error (404)

```json
{
  "message": "Subscription plan not found or inactive.",
  "success": false,
  "code": 404
}
```

---

## 3. My Subscriptions

| | |
|---|---|
| **Endpoint** | `GET /api/v1/subscriptions/my-subscriptions` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns the authenticated user's subscription history with pagination, plan details, and payment info.

### Query Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| status | string | No | — | — | Filter by subscription status: `active`, `expired`, `cancelled` |
| s_type | integer | No | — | — | Filter by subscription type: `1` = Portfolio, `2` = Note download |
| per_page | integer | No | 15 | min:1, max:50 | Number of items per page (capped at 50) |

### Example Request

```
GET /api/v1/subscriptions/my-subscriptions
GET /api/v1/subscriptions/my-subscriptions?status=active
GET /api/v1/subscriptions/my-subscriptions?s_type=1&per_page=20
GET /api/v1/subscriptions/my-subscriptions?status=expired&s_type=2&per_page=10
```

### Success (200)

```json
{
  "message": "User subscriptions retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "subscription_plan_id": 1,
        "payment_id": 1,
        "status": "active",
        "starts_at": "2024-01-01T00:00:00.000000Z",
        "expires_at": "2024-02-01T00:00:00.000000Z",
        "payment_method": "cashfree",
        "transaction_id": "TXN123456",
        "amount_paid": 99.00,
        "is_active": true,
        "days_remaining": 15,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "plan": {
          "id": 1,
          "name": "Basic Plan",
          "slug": "basic-plan",
          "price": 99.00,
          "currency": "INR",
          "billing_period": "monthly",
          "s_type": 1,
          "formatted_price": "INR 99.00"
        },
        "payment": {
          "id": 1,
          "order_id": "SUB_ABC123XYZ",
          "status": "success",
          "amount": 99.00,
          "currency": "INR",
          "formatted_amount": "INR 99.00"
        }
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42,
      "from": 1,
      "to": 15
    },
    "links": {
      "first": "https://api.example.com/api/v1/subscriptions/my-subscriptions?page=1",
      "last": "https://api.example.com/api/v1/subscriptions/my-subscriptions?page=3",
      "prev": null,
      "next": "https://api.example.com/api/v1/subscriptions/my-subscriptions?page=2"
    }
  }
}
```

---

## 4. Current Subscription

| | |
|---|---|
| **Endpoint** | `GET /api/v1/subscriptions/current` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns the user's current **active** subscription for the given subscription type. Returns `subscription: null` if none found.

### Query Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| s_type | integer | No | 1 | — | Subscription type: `1` = Portfolio, `2` = Note download |

### Example Request

```
GET /api/v1/subscriptions/current
GET /api/v1/subscriptions/current?s_type=1
GET /api/v1/subscriptions/current?s_type=2
```

### Success (200) – Has Active Subscription

```json
{
  "message": "Current subscription retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "subscription": {
      "id": 1,
      "user_id": 1,
      "subscription_plan_id": 1,
      "payment_id": 1,
      "status": "active",
      "starts_at": "2024-01-01T00:00:00.000000Z",
      "expires_at": "2024-02-01T00:00:00.000000Z",
      "payment_method": "cashfree",
      "transaction_id": "TXN123456",
      "amount_paid": 99.00,
      "is_active": true,
      "days_remaining": 15,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "plan": { },
      "payment": { }
    }
  }
}
```

### Success (200) – No Active Subscription

```json
{
  "message": "No active subscription found.",
  "success": true,
  "code": 200,
  "data": {
    "subscription": null
  }
}
```

---

## 5. Purchase Subscription

| | |
|---|---|
| **Endpoint** | `POST /api/v1/subscriptions/purchase` |
| **Content-Type** | `application/json` |
| **Access** | Protected (auth:sanctum) |

Creates a payment for a subscription plan and returns a checkout URL. User redirects to the URL to complete payment via Cashfree.

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| subscription_plan_id | integer | **Yes** | exists in subscription_plans, is_active=true | ID of the plan to purchase |

### Request Body

```json
{
  "subscription_plan_id": 1
}
```

### Success (200) – Checkout Created

```json
{
  "message": "Subscription payment created successfully.",
  "success": true,
  "code": 200,
  "data": {
    "payment": {
      "order_id": "SUB_ABC123XYZ",
      "amount": 99.00,
      "currency": "INR",
      "status": "pending"
    },
    "checkout_url": "https://api.example.com/api/v1/payment/checkout?order_id=SUB_ABC123XYZ",
    "payment_session_id": "session_abc123",
    "subscription_plan": {
      "id": 1,
      "name": "Basic Plan",
      "price": 99.00,
      "currency": "INR",
      "billing_period": "monthly"
    }
  }
}
```

### Success (200) – Already Paid

If the user already has a completed payment for this plan:

```json
{
  "message": "Subscription payment already completed.",
  "success": true,
  "code": 200,
  "data": {
    "order_id": "SUB_ABC123XYZ",
    "status": "already_paid",
    "subscription_plan": { }
  }
}
```

### Error (404)

```json
{
  "message": "Subscription plan not found or inactive.",
  "success": false,
  "code": 404
}
```

### Error (422) – Validation

```json
{
  "message": "Validation failed.",
  "success": false,
  "code": 422,
  "errors": {
    "subscription_plan_id": [
      "Please select a subscription plan.",
      "The selected subscription plan is not available or inactive."
    ]
  }
}
```

---

## 6. Cancel Subscription

| | |
|---|---|
| **Endpoint** | `PATCH /api/v1/subscriptions/{subscription}/cancel` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Cancels the authenticated user's active subscription. Only **active** subscriptions can be cancelled.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| subscription | integer | **Yes** | User subscription ID |

### Query Parameters

None.

### Example Request

```
PATCH /api/v1/subscriptions/5/cancel
```

### Success (200)

```json
{
  "message": "Subscription cancelled successfully.",
  "success": true,
  "code": 200,
  "data": {
    "subscription": {
      "id": 5,
      "user_id": 1,
      "subscription_plan_id": 1,
      "status": "cancelled",
      "starts_at": "2024-01-01T00:00:00.000000Z",
      "expires_at": "2024-02-01T00:00:00.000000Z",
      "is_active": false,
      "plan": { },
      "payment": { }
    }
  }
}
```

### Error (400)

```json
{
  "message": "Only active subscriptions can be cancelled.",
  "success": false,
  "code": 400
}
```

### Error (403)

```json
{
  "message": "You can only cancel your own subscriptions.",
  "success": false,
  "code": 403
}
```

### Error (404)

```json
{
  "message": "Subscription not found.",
  "success": false,
  "code": 404
}
```

---

## 7. Renew Subscription

| | |
|---|---|
| **Endpoint** | `POST /api/v1/subscriptions/{subscription}/renew` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Creates a renewal payment for an expired or cancelled subscription. Returns a checkout URL. Does **not** renew if the subscription is still active and unexpired.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| subscription | integer | **Yes** | User subscription ID (must be expired or cancelled) |

### Query Parameters

None.

### Example Request

```
POST /api/v1/subscriptions/5/renew
```

### Success (200) – Renewal Checkout Created

```json
{
  "message": "Subscription renewal payment created successfully.",
  "success": true,
  "code": 200,
  "data": {
    "payment": {
      "order_id": "SUB_DEF456GHI",
      "amount": 99.00,
      "currency": "INR",
      "status": "pending"
    },
    "checkout_url": "https://api.example.com/api/v1/payment/checkout?order_id=SUB_DEF456GHI",
    "payment_session_id": "session_def456",
    "subscription": {
      "id": 5,
      "status": "expired",
      "plan": { }
    }
  }
}
```

### Success (200) – Already Paid

```json
{
  "message": "Subscription renewal payment already completed.",
  "success": true,
  "code": 200,
  "data": {
    "order_id": "SUB_DEF456GHI",
    "status": "already_paid",
    "subscription": { }
  }
}
```

### Error (400)

```json
{
  "message": "This subscription is still active and does not need renewal.",
  "success": false,
  "code": 400
}
```

### Error (403)

```json
{
  "message": "You can only renew your own subscriptions.",
  "success": false,
  "code": 403
}
```

### Error (404)

```json
{
  "message": "Subscription plan not found.",
  "success": false,
  "code": 404
}
```

---

## Response Field Reference

### Subscription Plan Object

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Plan ID |
| name | string | Plan name |
| slug | string | URL-friendly unique slug |
| description | string \| null | Plan description |
| price | number | Price |
| currency | string | Currency code (e.g. INR) |
| billing_period | string | `monthly`, `yearly`, `lifetime` |
| max_images | integer | Max portfolio images for this plan |
| max_files | integer | Max portfolio files for this plan |
| features | array | List of feature strings |
| is_popular | boolean | Popular plan flag |
| is_active | boolean | Whether plan is active |
| sort_order | integer | Display order |
| s_type | integer | `1` = Portfolio, `2` = Note download |
| formatted_price | string | Human-readable price (e.g. "INR 99.00") |
| created_at | string | ISO 8601 datetime |
| updated_at | string | ISO 8601 datetime |

### User Subscription Object

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Subscription ID |
| user_id | integer | Owner user ID |
| subscription_plan_id | integer | Plan ID |
| payment_id | integer \| null | Associated payment ID |
| status | string | `active`, `expired`, `cancelled` |
| starts_at | string \| null | ISO 8601 datetime |
| expires_at | string \| null | ISO 8601 datetime (null for lifetime) |
| payment_method | string \| null | e.g. `cashfree` |
| transaction_id | string \| null | Gateway transaction ID |
| amount_paid | number | Amount paid |
| is_active | boolean | Computed: status active and not expired |
| days_remaining | integer \| null | Days until expiry (null if lifetime) |
| created_at | string | ISO 8601 datetime |
| updated_at | string | ISO 8601 datetime |
| plan | object | Subscription plan (when loaded) |
| payment | object | Payment record (when loaded) |

### Subscription Types (s_type)

| Value | Description |
|-------|-------------|
| 1 | Portfolio plans (max_images, max_files for portfolio) |
| 2 | Note download plans |

### Subscription Status

| Status | Description |
|--------|-------------|
| active | Currently active and within validity period |
| expired | Past expires_at |
| cancelled | User cancelled the subscription |

---

## Example Requests

### List Plans (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/subscriptions/plans?s_type=1" \
  -H "Accept: application/json"
```

### Get Plan (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/subscriptions/plans/basic-plan" \
  -H "Accept: application/json"
```

### My Subscriptions (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/subscriptions/my-subscriptions?status=active&per_page=20" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Current Subscription (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/subscriptions/current?s_type=1" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Purchase (cURL)

```bash
curl -X POST "https://api.example.com/api/v1/subscriptions/purchase" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"subscription_plan_id": 1}'
```

### Cancel (cURL)

```bash
curl -X PATCH "https://api.example.com/api/v1/subscriptions/5/cancel" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Renew (cURL)

```bash
curl -X POST "https://api.example.com/api/v1/subscriptions/5/renew" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript (Fetch – Purchase)

```javascript
const response = await fetch('/api/v1/subscriptions/purchase', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({ subscription_plan_id: 1 }),
});
const data = await response.json();
if (data.success && data.data.checkout_url) {
  window.location.href = data.data.checkout_url;
}
```

### JavaScript (Fetch – My Subscriptions with Filters)

```javascript
const params = new URLSearchParams({
  status: 'active',
  s_type: 1,
  per_page: 20,
});
const response = await fetch(`/api/v1/subscriptions/my-subscriptions?${params}`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});
```

---

## Error Codes Summary

| Code | Condition |
|------|-----------|
| 400 | Bad request (e.g. cancel non-active, renew active subscription) |
| 401 | Unauthenticated (missing/invalid Bearer token) |
| 403 | Forbidden (accessing another user's subscription) |
| 404 | Plan/subscription not found or inactive |
| 422 | Validation failed (e.g. invalid subscription_plan_id) |
| 500 | Server error (e.g. payment gateway failure) |
