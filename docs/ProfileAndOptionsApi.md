# Profile & Options API Documentation

Complete reference for the Options API and Profile API endpoints in the SuGanta API.

**Base path:** `/api/v1`  
**Content-Type:** `application/json` (except avatar upload: `multipart/form-data`)

---

## Table of Contents

1. [Options API](#options-api)
2. [Profile API](#profile-api)
3. [Response Format](#response-format)
4. [Error Codes](#error-codes)

---

# Options API

Public endpoint for retrieving configuration options (dropdowns, select boxes). No authentication required. Responses are cached for 24 hours and support ETag for conditional requests.

---

## Get Options

| | |
|---|---|
| **Endpoint** | `GET /api/v1/options` |
| **Access** | Public (no auth) |
| **Cache** | 24 hours, supports `If-None-Match` (ETag) |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `key` | string | No | Comma-separated option keys. Omit to return all options. Example: `gender,board,stream` |

### Available Option Keys

| Key | Description | IDs / Values |
|-----|-------------|--------------|
| `gender` | Gender options | 1–4 (Male, Female, Other, Prefer not to say) |
| `institute_type` | Institute type | 1–5 (School, College, University, Coaching Center, NGO) |
| `institute_category` | Institute category | 1–3 (Government, Private, Aided) |
| `current_class` | Student class levels | 1–14 (Class 1–12, Undergraduate, Postgraduate) |
| `board` | Education board | 1–5 (CBSE, ICSE, State Board, IB, IGCSE) |
| `stream` | Student stream | 1–6 (Science, Commerce, Arts, Computer Science, Engineering, Medical) |
| `teaching_mode` | Teaching mode | 1–5 (Online Only, Offline Only, Both, Google Meet, Zoom) |
| `availability_status` | Availability | 1–5 (Available, Busy, Unavailable, On Leave, In a Meeting) |
| `timezone` | Timezone identifiers | 1–10 (e.g. Asia/Kolkata, UTC, America/New_York) |
| `country` | Countries | 1+ (e.g. 1=India) |
| `highest_qualification` | Qualifications | 1–15 (High School, Diploma, Bachelors, Masters, Ph.D., etc.) |
| `field_of_study` | Fields of study | 1–20 (Computer Science, Mathematics, Physics, etc.) |
| `teaching_experience_years` | Experience ranges | 1–15 (1 Year … 30+ Years) |
| `travel_radius_km` | Travel radius (km) | 0,1–10,15,20,25,30,40,50,75,100 |
| `hourly_rate_range` | Hourly rate (₹) | 0–10 (Not Specified, ₹100-200 … ₹1000+) |
| `monthly_rate_range` | Monthly rate (₹) | 0–10 (Not Specified, ₹1000-2000 … ₹10000+) |
| `budget_range` | Budget range (₹) | 1–10 |
| `establishment_year_range` | Institute establishment | 1–9 |
| `total_students_range` | Student count range | 1–8 |
| `total_teachers_range` | Teacher count range | 1–8 |
| `teaching_mode_enum` | Teaching mode (key-based) | online, in-person, hybrid, google_meet, zoom_meetings |

### Success Response (200)

```json
{
  "message": "Options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "gender": {
      "1": "Male",
      "2": "Female",
      "3": "Other",
      "4": "Prefer not to say"
    },
    "board": {
      "1": "CBSE",
      "2": "ICSE",
      "3": "State Board",
      "4": "IB",
      "5": "IGCSE"
    }
  }
}
```

**Response headers:** `Cache-Control: public, max-age=86400`, `ETag`

### Not Modified (304)

When `If-None-Match` matches current ETag, returns `304 Not Modified` with no body.

### Error (404)

```json
{
  "message": "No valid options found for the provided keys.",
  "success": false,
  "code": 404
}
```

### Example Requests

```bash
# All options
curl -X GET "https://api.example.com/api/v1/options" \
  -H "Accept: application/json"

# Specific keys
curl -X GET "https://api.example.com/api/v1/options?key=gender,board,stream" \
  -H "Accept: application/json"

# With ETag (conditional)
curl -X GET "https://api.example.com/api/v1/options" \
  -H "Accept: application/json" \
  -H "If-None-Match: \"abc123etag\""
```

---

# Profile API

All profile endpoints operate on the authenticated user's own profile. They require a Sanctum Bearer token.

**Base path:** `/api/v1/profile`  
**Authentication:** `Authorization: Bearer {token}`

---

## Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/profile` | Get full profile |
| GET | `/api/v1/profile/form-autofill` | Get form auto-fill data |
| PUT/PATCH | `/api/v1/profile` | Update basic profile |
| PUT/PATCH | `/api/v1/profile/location` | Update address & location |
| PUT/PATCH | `/api/v1/profile/social` | Update social media links |
| PUT/PATCH | `/api/v1/profile/teaching` | Update teaching information |
| PUT/PATCH | `/api/v1/profile/institute` | Update institute information |
| PUT/PATCH | `/api/v1/profile/student` | Update student information |
| PUT/POST | `/api/v1/profile/avatar` | Update profile picture |
| PUT/PATCH | `/api/v1/profile/password` | Change password |
| PUT/PATCH | `/api/v1/profile/preferences` | Update preferences |
| GET | `/api/v1/profile/completion` | Get completion data |
| POST | `/api/v1/profile/refresh` | Force refresh profile |
| POST | `/api/v1/profile/cache/clear` | Clear profile caches |
| DELETE | `/api/v1/profile` | Delete account (permanent) |

---

## 1. Get Profile

| | |
|---|---|
| **Endpoint** | `GET /api/v1/profile` |
| **Access** | Protected (`auth:sanctum`) |

### Parameters

None (uses authenticated user).

### Success (200)

```json
{
  "message": "Profile retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "teacher",
      "email_verified_at": "2025-01-15T10:00:00.000000Z"
    },
    "profile": { },
    "profile_image_url": "https://...",
    "completion_percentage": 75
  }
}
```

---

## 2. Get Form Auto-Fill Data

> **Full documentation:** See [Form Auto-Fill API](./FormAutofillApi.md) for detailed field reference, integration examples, and use cases.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/profile/form-autofill` |
| **Access** | Protected (`auth:sanctum`) |

Returns the authenticated user's profile data for pre-populating form inputs. Data is grouped by section (default) or returned as a flat key-value object. Teaching, student, and institute sections return empty `null` fields when no record exists, so forms can display all fields for first-time fill.

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sections` | string | No | Comma-separated sections to include. Omit to return all. Valid: `basic`, `location`, `social`, `teaching`, `student`, `institute` |
| `format` | string | No | `sections` (default) – data grouped by section; `flat` – merged key-value for all sections |

### Sections

| Section | Fields Returned |
|---------|-----------------|
| `basic` | first_name, last_name, display_name, email, bio, date_of_birth, gender_id, nationality, phone_primary, phone_secondary, whatsapp, website, emergency_contact_name, emergency_contact_phone |
| `location` | address_line_1, address_line_2, area, city, state, pincode, country_id, latitude, longitude |
| `social` | facebook_url, twitter_url, instagram_url, linkedin_url, youtube_url, tiktok_url, telegram_username, discord_username, github_url, portfolio_url, blog_url |
| `teaching` | highest_qualification, institution_name, field_of_study, graduation_year, teaching_experience_years, hourly_rate_id, monthly_rate_id, travel_radius_km_id, teaching_mode_id, availability_status_id, teaching_philosophy, subjects_taught |
| `student` | current_class_id, current_school, board_id, stream_id, parent_name, parent_phone, parent_email, budget_min, budget_max, learning_challenges |
| `institute` | institute_name, institute_type_id, institute_category_id, affiliation_number, registration_number, establishment_year_id, principal_name, principal_phone, principal_email, total_students_id, total_teachers_id, total_branches, institute_description |

### Success (200) – Format: sections (default)

```json
{
  "message": "Form auto-fill data retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "form_data": {
      "basic": {
        "first_name": "John",
        "last_name": "Doe",
        "display_name": "John Doe",
        "email": "john@example.com",
        "bio": null,
        "date_of_birth": "1990-05-15",
        "gender_id": 1,
        "nationality": "Indian",
        "phone_primary": "+919876543210",
        "phone_secondary": null,
        "whatsapp": "+919876543210",
        "website": null,
        "emergency_contact_name": null,
        "emergency_contact_phone": null
      },
      "location": {
        "address_line_1": "123 Main St",
        "address_line_2": null,
        "area": "Andheri West",
        "city": "Mumbai",
        "state": "Maharashtra",
        "pincode": "400058",
        "country_id": 1,
        "latitude": null,
        "longitude": null
      },
      "social": { },
      "teaching": {
        "highest_qualification": "M.Sc.",
        "institution_name": "University of Mumbai",
        "field_of_study": "Mathematics",
        "graduation_year": 2015,
        "teaching_experience_years": 5,
        "hourly_rate_id": 4,
        "monthly_rate_id": 3,
        "travel_radius_km_id": 5,
        "teaching_mode_id": 3,
        "availability_status_id": 1,
        "teaching_philosophy": null,
        "subjects_taught": [1, 2, 3]
      }
    },
    "sections_included": ["basic", "location", "social", "teaching", "student", "institute"],
    "format": "sections",
    "profile_image_url": "https://..."
  }
}
```

### Success (200) – Format: flat

```json
{
  "message": "Form auto-fill data retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "form_data": {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone_primary": "+919876543210",
      "city": "Mumbai",
      "state": "Maharashtra"
    },
    "sections_included": ["basic", "location"],
    "format": "flat",
    "profile_image_url": "https://..."
  }
}
```

### Example Requests

```bash
# All sections, grouped by section (default)
curl -X GET "https://api.example.com/api/v1/profile/form-autofill" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Flat format (merged key-value)
curl -X GET "https://api.example.com/api/v1/profile/form-autofill?format=flat" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Contact form only (basic + location)
curl -X GET "https://api.example.com/api/v1/profile/form-autofill?sections=basic,location" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Teaching section only, flat format
curl -X GET "https://api.example.com/api/v1/profile/form-autofill?sections=teaching&format=flat" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## 3. Update Basic Profile

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile` or `PATCH /api/v1/profile` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `first_name` | string | **Yes** | max:255 |
| `last_name` | string | No | max:255 |
| `display_name` | string | No | max:255 |
| `bio` | string | No | max:1000 |
| `date_of_birth` | string | No | valid date |
| `gender_id` | integer | No | in:1,2,3,4 |
| `nationality` | string | No | max:255 |
| `phone_primary` | string | No | max:20 |
| `phone_secondary` | string | No | max:20 |
| `whatsapp` | string | No | max:20 |
| `website` | string | No | url, max:255 |
| `emergency_contact_name` | string | No | max:255 |
| `emergency_contact_phone` | string | No | max:20 |
| `email` | string | **Yes** | email, unique (excluding current user) |

---

## 3. Update Location

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/location` or `PATCH /api/v1/profile/location` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `address_line_1` | string | No | max:255 |
| `address_line_2` | string | No | max:255 |
| `area` | string | No | max:255 |
| `city` | string | No | max:255 |
| `state` | string | No | max:255 |
| `pincode` | string | No | max:20 |
| `country_id` | integer | No | in:1,2,3,4,5,6,7,8,9,10 |
| `latitude` | numeric | No | between:-90,90 |
| `longitude` | numeric | No | between:-180,180 |

---

## 4. Update Social Links

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/social` or `PATCH /api/v1/profile/social` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `facebook_url` | string | No | url, max:255 |
| `twitter_url` | string | No | url, max:255 |
| `instagram_url` | string | No | url, max:255 |
| `linkedin_url` | string | No | url, max:255 |
| `youtube_url` | string | No | url, max:255 |
| `tiktok_url` | string | No | url, max:255 |
| `telegram_username` | string | No | max:255 |
| `discord_username` | string | No | max:255 |
| `github_url` | string | No | url, max:255 |
| `portfolio_url` | string | No | url, max:255 |
| `blog_url` | string | No | url, max:255 |
| `website` | string | No | url, max:255 |
| `whatsapp` | string | No | max:20 |

---

## 5. Update Teaching Info

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/teaching` or `PATCH /api/v1/profile/teaching` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `highest_qualification` | string | No | max:255 |
| `institution_name` | string | No | max:255 |
| `field_of_study` | string | No | max:255 |
| `graduation_year` | integer | No | min:1950, max:current_year+5 |
| `teaching_experience_years` | integer | No | min:0, max:50 |
| `hourly_rate_id` | integer | No | in:1,2,3,4,5,6,7,8,9,10 |
| `monthly_rate_id` | integer | No | in:1,2,3,4,5,6,7,8,9,10 |
| `travel_radius_km_id` | integer | No | in:0,1,2,3,4,5,6,7,8,9,10,15,20,25,30,40,50,75,100 |
| `teaching_mode_id` | integer | No | in:1,2,3 |
| `availability_status_id` | integer | No | in:1,2,3 |
| `teaching_philosophy` | string | No | max:2000 |
| `subjects_taught` | array | No | array of integers, exists:subjects,id |

---

## 6. Update Institute Info

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/institute` or `PATCH /api/v1/profile/institute` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `institute_name` | string | **Yes** | max:255 |
| `institute_type_id` | integer | **Yes** | in:1,2,3,4,5 |
| `institute_category_id` | integer | No | in:1,2,3 |
| `affiliation_number` | string | No | max:255 |
| `registration_number` | string | No | max:255 |
| `establishment_year_id` | integer | No | in:1,2,3,4,5,6,7,8,9 |
| `principal_name` | string | No | max:255 |
| `principal_phone` | string | No | max:20 |
| `principal_email` | string | No | email, max:255 |
| `total_students_id` | integer | No | in:1,2,3,4,5,6,7,8 |
| `total_teachers_id` | integer | No | in:1,2,3,4,5,6,7,8 |
| `total_branches` | integer | No | min:1 |
| `institute_description` | string | No | max:2000 |

---

## 7. Update Student Info

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/student` or `PATCH /api/v1/profile/student` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `current_class_id` | integer | No | in:1,2,3,4,5,6,7,8,9,10,11,12,13,14 |
| `current_school` | string | No | max:255 |
| `board_id` | integer | No | in:1,2,3,4,5 |
| `stream_id` | integer | No | in:1,2,3,4,5,6 |
| `parent_name` | string | No | max:255 |
| `parent_phone` | string | No | max:20 |
| `parent_email` | string | No | email, max:255 |
| `budget_min` | numeric | No | min:0 |
| `budget_max` | numeric | No | min:0 |
| `learning_challenges` | string | No | max:1000 |

---

## 8. Update Avatar

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/avatar` or `POST /api/v1/profile/avatar` |
| **Access** | Protected |
| **Content-Type** | `multipart/form-data` |

### Request Body (Form Data)

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `avatar` | file | **Yes** | image, mimes:jpeg,png,jpg,gif, max:2048 KB |

### Success (200)

```json
{
  "message": "Profile picture updated successfully.",
  "success": true,
  "code": 200,
  "data": {
    "profile_image": "profile-images/profile_1_xxx.jpg",
    "profile_image_url": "https://..."
  }
}
```

---

## 9. Update Password

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/password` or `PATCH /api/v1/profile/password` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `current_password` | string | **Yes** | Must match user's current password |
| `password` | string | **Yes** | min:8, confirmed |
| `password_confirmation` | string | **Yes** | Must match `password` |

**Strength rules:** Password must score ≥3 (length ≥8/12, lowercase, uppercase, digit, special character).

---

## 10. Update Preferences

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/profile/preferences` or `PATCH /api/v1/profile/preferences` |
| **Access** | Protected |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `theme` | string | No | in:light,dark,auto |
| `language` | string | No | max:10 |
| `notifications` | object | No | arbitrary object |
| `privacy_settings` | object | No | arbitrary object |

---

## 11. Get Completion Data

| | |
|---|---|
| **Endpoint** | `GET /api/v1/profile/completion` |
| **Access** | Protected |

### Parameters

None. Result is cached for 2 minutes.

### Success (200)

```json
{
  "message": "Completion data retrieved.",
  "success": true,
  "code": 200,
  "data": {
    "percentage": 75,
    "status": "Detailed",
    "color": "info",
    "completion_summary": {
      "total_fields": 15,
      "completed_fields": 11,
      "completion_percentage": 75,
      "high_priority_completed": 4,
      "high_priority_total": 5,
      "status": "Detailed",
      "color": "info",
      "next_priority_fields": [ ]
    },
    "cached": false
  }
}
```

---

## 12. Refresh Profile

| | |
|---|---|
| **Endpoint** | `POST /api/v1/profile/refresh` |
| **Access** | Protected |

### Parameters

None. Forces reload and clears profile caches.

### Success (200)

Returns full formatted profile data in `data`.

---

## 13. Clear Cache

| | |
|---|---|
| **Endpoint** | `POST /api/v1/profile/cache/clear` |
| **Access** | Protected |

### Parameters

None. Clears profile-related caches for the authenticated user.

### Success (200)

```json
{
  "message": "Profile caches cleared successfully.",
  "success": true,
  "code": 200
}
```

---

## 14. Delete Account

| | |
|---|---|
| **Endpoint** | `DELETE /api/v1/profile` |
| **Access** | Protected |
| **Warning** | Irreversible. All tokens are revoked before deletion. |

### Request Body Parameters

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `password` | string | **Yes** | Must match current password |
| `confirmation` | string | **Yes** | Must be exactly `DELETE` |
| `reason` | string | No | max:500 |

### Success (200)

```json
{
  "message": "Your account has been permanently deleted.",
  "success": true,
  "code": 200,
  "data": null
}
```

---

# Response Format

### Success

```json
{
  "message": "...",
  "success": true,
  "code": 200,
  "data": { }
}
```

### Validation Error (422)

```json
{
  "message": "Validation failed.",
  "success": false,
  "code": 422,
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

---

# Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 304 | Not Modified (Options API with matching ETag) |
| 401 | Unauthenticated |
| 404 | Not found (e.g. invalid option keys) |
| 422 | Validation failed |
| 500 | Internal server error |

---

*Last updated: March 12, 2026*
