# Form Auto-Fill API

API for retrieving the authenticated user's profile data in a format suitable for pre-populating form inputs. Use this when rendering contact forms, profile edit forms, lead forms, or any form that benefits from auto-filling with the user's saved data.

**Base path:** `/api/v1/profile`  
**Authentication:** Required — `Authorization: Bearer {token}`

---

## Table of Contents

1. [Overview](#overview)
2. [Endpoint Summary](#endpoint-summary)
3. [Get Form Auto-Fill Data](#get-form-auto-fill-data)
4. [Query Parameters](#query-parameters)
5. [Sections Reference](#sections-reference)
6. [Response Structure](#response-structure)
7. [Examples](#examples)
8. [Integration Guide](#integration-guide)
9. [Error Handling](#error-handling)
10. [Related APIs](#related-apis)

---

## Overview

The Form Auto-Fill API returns profile data as **field-name → value** pairs so frontend forms can pre-populate inputs without fetching the full profile. Data can be:

- **Grouped by section** (`format=sections`) — ideal for multi-step or tabbed forms
- **Flat** (`format=flat`) — single merged object for simple one-step forms

Sections (`basic`, `location`, `social`, `teaching`, `student`, `institute`) can be requested individually or combined. Teaching, student, and institute sections always return a full field set — missing records yield `null` values, so forms can render all fields for first-time fill.

---

## Endpoint Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/profile/form-autofill` | Retrieve form auto-fill data for the authenticated user |

---

## Get Form Auto-Fill Data

| | |
|---|---|
| **Endpoint** | `GET /api/v1/profile/form-autofill` |
| **Access** | Protected (`auth:sanctum`) |
| **Content-Type** | `application/json` |

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `sections` | string | No | All sections | Comma-separated list of sections to include. Valid values: `basic`, `location`, `social`, `teaching`, `student`, `institute` |
| `format` | string | No | `sections` | `sections` — data grouped by section name; `flat` — merged into a single object |

### Section Validation

- Invalid section names are **ignored**
- If all requested sections are invalid, **all sections** are returned
- Section names are case-sensitive; use lowercase

### Example URLs

```
GET /api/v1/profile/form-autofill
GET /api/v1/profile/form-autofill?sections=basic,location
GET /api/v1/profile/form-autofill?sections=teaching&format=flat
GET /api/v1/profile/form-autofill?format=flat
```

---

## Sections Reference

### Section: `basic`

Personal and contact information.

| Field | Type | Description | Options / Validation |
|-------|------|-------------|---------------------|
| `first_name` | string \| null | First name | max:255 |
| `last_name` | string \| null | Last name | max:255 |
| `display_name` | string \| null | Display name | max:255 |
| `email` | string \| null | User email (from `users` table) | email |
| `bio` | string \| null | Short bio | max:1000 |
| `date_of_birth` | string \| null | Date of birth | YYYY-MM-DD |
| `gender_id` | integer \| null | Gender option ID | 1–4 (see Options API: `gender`) |
| `nationality` | string \| null | Nationality | max:255 |
| `phone_primary` | string \| null | Primary phone | max:20 |
| `phone_secondary` | string \| null | Secondary phone | max:20 |
| `whatsapp` | string \| null | WhatsApp number | max:20 |
| `website` | string \| null | Personal website | url, max:255 |
| `emergency_contact_name` | string \| null | Emergency contact name | max:255 |
| `emergency_contact_phone` | string \| null | Emergency contact phone | max:20 |

---

### Section: `location`

Address and geographic data.

| Field | Type | Description | Options / Validation |
|-------|------|-------------|---------------------|
| `address_line_1` | string \| null | Street address line 1 | max:255 |
| `address_line_2` | string \| null | Street address line 2 | max:255 |
| `area` | string \| null | Area / locality | max:255 |
| `city` | string \| null | City | max:255 |
| `state` | string \| null | State | max:255 |
| `pincode` | string \| null | PIN / ZIP code | max:20 |
| `country_id` | integer \| null | Country option ID | 1–10 (see Options API: `country`) |
| `latitude` | float \| null | Latitude | -90 to 90 |
| `longitude` | float \| null | Longitude | -180 to 180 |

---

### Section: `social`

Social media and portfolio links.

| Field | Type | Description | Validation |
|-------|------|-------------|------------|
| `facebook_url` | string \| null | Facebook profile URL | url, max:255 |
| `twitter_url` | string \| null | Twitter / X profile URL | url, max:255 |
| `instagram_url` | string \| null | Instagram profile URL | url, max:255 |
| `linkedin_url` | string \| null | LinkedIn profile URL | url, max:255 |
| `youtube_url` | string \| null | YouTube channel URL | url, max:255 |
| `tiktok_url` | string \| null | TikTok profile URL | url, max:255 |
| `telegram_username` | string \| null | Telegram username | max:255 |
| `discord_username` | string \| null | Discord username | max:255 |
| `github_url` | string \| null | GitHub profile URL | url, max:255 |
| `portfolio_url` | string \| null | Portfolio website URL | url, max:255 |
| `blog_url` | string \| null | Blog URL | url, max:255 |

---

### Section: `teaching`

Teaching profile (teachers). Returns full field structure even if no teaching record exists; missing values are `null`.

| Field | Type | Description | Options / Validation |
|-------|------|-------------|---------------------|
| `highest_qualification` | string \| null | Highest qualification | max:255 |
| `institution_name` | string \| null | Institution where qualified | max:255 |
| `field_of_study` | string \| null | Field of study | max:255 |
| `graduation_year` | integer \| null | Year of graduation | 1950–(current+5) |
| `teaching_experience_years` | integer \| null | Years of teaching experience | 0–50 |
| `hourly_rate_id` | integer \| null | Hourly rate range option ID | 1–10 (see Options API: `hourly_rate_range`) |
| `monthly_rate_id` | integer \| null | Monthly rate range option ID | 1–10 (see Options API: `monthly_rate_range`) |
| `travel_radius_km_id` | integer \| null | Travel radius option ID | 0,1–10,15,20,25,30,40,50,75,100 |
| `teaching_mode_id` | integer \| null | Teaching mode option ID | 1–3 (Online, Offline, Both) |
| `availability_status_id` | integer \| null | Availability option ID | 1–3 (see Options API: `availability_status`) |
| `teaching_philosophy` | string \| null | Teaching philosophy text | max:2000 |
| `subjects_taught` | array | Subject IDs taught | Array of integers, `exists:subjects,id` |

---

### Section: `student`

Student profile. Returns full field structure even if no student record exists; missing values are `null`.

| Field | Type | Description | Options / Validation |
|-------|------|-------------|---------------------|
| `current_class_id` | integer \| null | Current class option ID | 1–14 (see Options API: `current_class`) |
| `current_school` | string \| null | Current school name | max:255 |
| `board_id` | integer \| null | Education board option ID | 1–5 (see Options API: `board`) |
| `stream_id` | integer \| null | Stream option ID | 1–6 (see Options API: `stream`) |
| `parent_name` | string \| null | Parent/guardian name | max:255 |
| `parent_phone` | string \| null | Parent phone | max:20 |
| `parent_email` | string \| null | Parent email | email, max:255 |
| `budget_min` | number \| null | Minimum budget (₹) | min:0 |
| `budget_max` | number \| null | Maximum budget (₹) | min:0 |
| `learning_challenges` | string \| null | Learning challenges / notes | max:1000 |

---

### Section: `institute`

Institute profile. Returns full field structure even if no institute record exists; missing values are `null`.

| Field | Type | Description | Options / Validation |
|-------|------|-------------|---------------------|
| `institute_name` | string \| null | Institute name | max:255 |
| `institute_type_id` | integer \| null | Institute type option ID | 1–5 (see Options API: `institute_type`) |
| `institute_category_id` | integer \| null | Institute category option ID | 1–3 (see Options API: `institute_category`) |
| `affiliation_number` | string \| null | Affiliation number | max:255 |
| `registration_number` | string \| null | Registration number | max:255 |
| `establishment_year_id` | integer \| null | Establishment year range ID | 1–9 |
| `principal_name` | string \| null | Principal name | max:255 |
| `principal_phone` | string \| null | Principal phone | max:20 |
| `principal_email` | string \| null | Principal email | email, max:255 |
| `total_students_id` | integer \| null | Total students range ID | 1–8 |
| `total_teachers_id` | integer \| null | Total teachers range ID | 1–8 |
| `total_branches` | integer \| null | Number of branches | min:1 |
| `institute_description` | string \| null | Institute description | max:2000 |

---

## Response Structure

### Top-Level Response

All responses use the standard API envelope:

| Field | Type | Description |
|-------|------|-------------|
| `message` | string | Human-readable status message |
| `success` | boolean | Whether the request succeeded |
| `code` | integer | HTTP status code |
| `data` | object | Response payload |

### Data Object

| Field | Type | Description |
|-------|------|-------------|
| `form_data` | object | Auto-fill data (structure depends on `format`) |
| `sections_included` | array | List of section names included in the response |
| `format` | string | `sections` or `flat` |
| `profile_image_url` | string \| null | Full URL of the user's profile image |

---

## Examples

### Format: `sections` (default)

Data grouped by section — best for multi-step or tabbed forms.

**Request:**
```http
GET /api/v1/profile/form-autofill
Authorization: Bearer {token}
```

**Response (200):**
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
      "social": {},
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
      },
      "student": {},
      "institute": {}
    },
    "sections_included": ["basic", "location", "social", "teaching", "student", "institute"],
    "format": "sections",
    "profile_image_url": "https://cdn.example.com/profile-images/profile_1_abc.jpg"
  }
}
```

---

### Format: `flat`

All fields merged into one object — best for single-step forms.

**Request:**
```http
GET /api/v1/profile/form-autofill?format=flat
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Form auto-fill data retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "form_data": {
      "first_name": "John",
      "last_name": "Doe",
      "display_name": "John Doe",
      "email": "john@example.com",
      "phone_primary": "+919876543210",
      "address_line_1": "123 Main St",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400058",
      "country_id": 1
    },
    "sections_included": ["basic", "location", "social", "teaching", "student", "institute"],
    "format": "flat",
    "profile_image_url": "https://cdn.example.com/profile-images/profile_1_abc.jpg"
  }
}
```

---

### Partial Sections

Request only the sections you need.

**Request (contact form):**
```http
GET /api/v1/profile/form-autofill?sections=basic,location
Authorization: Bearer {token}
```

**Request (teaching profile form):**
```http
GET /api/v1/profile/form-autofill?sections=basic,teaching&format=flat
Authorization: Bearer {token}
```

---

### cURL Examples

```bash
# All sections, grouped
curl -X GET "https://api.example.com/api/v1/profile/form-autofill" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Contact form (basic + location), flat format
curl -X GET "https://api.example.com/api/v1/profile/form-autofill?sections=basic,location&format=flat" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Teaching section only
curl -X GET "https://api.example.com/api/v1/profile/form-autofill?sections=teaching" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Integration Guide

### When to Use `format=sections`

- Multi-step or wizard forms
- Tabbed profile sections
- When you need to group fields by category

### When to Use `format=flat`

- Single-page contact or lead forms
- Forms with all fields in one view
- When you prefer a simple `field_name → value` map

### React / JavaScript Example (sections format)

```javascript
// Fetch form auto-fill data
const response = await fetch('/api/v1/profile/form-autofill?sections=basic,location', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});
const { data } = await response.json();

// Populate form by section
const basicFields = data.form_data.basic;
const locationFields = data.form_data.location;

// Example: set input values
document.querySelector('[name="first_name"]').value = basicFields.first_name ?? '';
document.querySelector('[name="email"]').value = basicFields.email ?? '';
document.querySelector('[name="city"]').value = locationFields.city ?? '';
```

### React / JavaScript Example (flat format)

```javascript
const response = await fetch('/api/v1/profile/form-autofill?format=flat&sections=basic,location', {
  headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
});
const { data } = await response.json();
const formData = data.form_data;

// Iterate over fields and populate form
Object.entries(formData).forEach(([name, value]) => {
  const input = document.querySelector(`[name="${name}"]`);
  if (input && value != null) input.value = value;
});
```

---

## Error Handling

### 401 Unauthorized

Missing or invalid Bearer token.

```json
{
  "message": "Unauthenticated."
}
```

### 500 Internal Server Error

Server error while building the response.

```json
{
  "message": "Unable to retrieve form auto-fill data.",
  "success": false,
  "code": 500
}
```

---

## Related APIs

| API | Purpose |
|-----|---------|
| [Options API](./ProfileAndOptionsApi.md#options-api) | Fetch dropdown labels for `*_id` fields (e.g. `gender`, `country`, `board`) |
| [Profile API](./ProfileAndOptionsApi.md) | Full profile read/write; use for saving form submissions |
| [Profile Update Endpoints](./ProfileAndOptionsApi.md) | Section-specific updates: `PUT /profile`, `PUT /profile/location`, etc. |

---

## Quick Reference

| Request | Use Case |
|---------|----------|
| `GET /profile/form-autofill` | Full profile form (all sections, grouped) |
| `GET /profile/form-autofill?format=flat` | Full profile form (all sections, flat) |
| `GET /profile/form-autofill?sections=basic,location` | Contact / lead form |
| `GET /profile/form-autofill?sections=teaching&format=flat` | Teaching profile form only |
| `GET /profile/form-autofill?sections=student` | Student profile form only |
| `GET /profile/form-autofill?sections=institute` | Institute profile form only |

---

*Last updated: March 12, 2026*
