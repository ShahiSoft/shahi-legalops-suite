# ShahiTemplate REST API Documentation

**Version:** 1.0.0  
**API Namespace:** `shahi-template/v1`  
**Base URL:** `https://your-site.com/wp-json/shahi-template/v1`

---

## Authentication

All API endpoints require authentication. The REST API uses WordPress's built-in authentication system:

- **Cookie Authentication**: For logged-in users making requests from the same site
- **Application Passwords**: For external applications (WordPress 5.6+)
- **Basic Auth Plugin**: For development environments

### Required Permissions

- **Admin Endpoints**: Require `manage_shahi_template` capability
- **Editor Endpoints**: Require `edit_shahi_settings` capability
- **Authenticated Endpoints**: Require user to be logged in

---

## Response Format

### Success Response
```json
{
    "success": true,
    "data": { /* response data */ },
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "code": "shahi_api_error",
    "message": "Error description",
    "data": {
        "status": 400
    }
}
```

---

## Analytics Endpoints

### GET /analytics/stats
Get analytics statistics for a specified period.

**Permission:** Editor

**Parameters:**
- `period` (string, optional): Time period - `7days`, `30days`, `90days`, `all` (default: `30days`)

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/analytics/stats?period=30days" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "period": "30days",
        "total_events": 1250,
        "unique_users": 45,
        "events_by_type": [
            {"event_type": "page_view", "count": "850"},
            {"event_type": "module_toggle", "count": "200"}
        ],
        "recent_events": [...]
    },
    "message": "Analytics stats retrieved successfully"
}
```

---

### GET /analytics/events
Get list of analytics events with filtering.

**Permission:** Editor

**Parameters:**
- `event_type` (string, optional): Filter by event type
- `limit` (integer, optional): Number of events (1-1000, default: 100)
- `offset` (integer, optional): Pagination offset (default: 0)

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/analytics/events?limit=50&offset=0" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "events": [...],
        "total": 1250,
        "limit": 50,
        "offset": 0
    },
    "message": "Events retrieved successfully"
}
```

---

### POST /analytics/track
Track a new analytics event.

**Permission:** Authenticated

**Parameters:**
- `event_type` (string, required): Type of event
- `event_data` (object, optional): Additional event data

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/analytics/track" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "custom_action",
    "event_data": {
        "action": "button_click",
        "page": "dashboard"
    }
  }'
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "event_id": 12345
    },
    "message": "Event tracked successfully"
}
```

---

## Modules Endpoints

### GET /modules
Get list of all modules.

**Permission:** Editor

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/modules" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "landing_pages": {
            "id": "landing_pages",
            "name": "Landing Pages",
            "description": "Create custom landing pages",
            "enabled": true,
            "settings": {}
        },
        "analytics": {...}
    },
    "message": "Modules retrieved successfully"
}
```

---

### GET /modules/{id}
Get details of a specific module.

**Permission:** Editor

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/modules/landing_pages" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

---

### POST /modules/{id}/enable
Enable a specific module.

**Permission:** Admin

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/modules/landing_pages/enable" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "id": "landing_pages",
        "enabled": true
    },
    "message": "Module enabled successfully"
}
```

---

### POST /modules/{id}/disable
Disable a specific module.

**Permission:** Admin

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/modules/landing_pages/disable" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

---

### PUT /modules/{id}/settings
Update module settings.

**Permission:** Admin

**Parameters:**
- `settings` (object, required): Module settings to update

**Example Request:**
```bash
curl -X PUT "https://your-site.com/wp-json/shahi-template/v1/modules/landing_pages/settings" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": {
        "max_pages": 10,
        "enable_templates": true
    }
  }'
```

---

## Settings Endpoints

### GET /settings
Get all plugin settings.

**Permission:** Editor

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/settings" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "plugin_name": "ShahiTemplate",
        "enable_debug": false,
        "enable_analytics": true,
        ...
    },
    "message": "Settings retrieved successfully"
}
```

---

### PUT /settings
Update plugin settings.

**Permission:** Admin

**Parameters:**
- `settings` (object, required): Settings to update

**Example Request:**
```bash
curl -X PUT "https://your-site.com/wp-json/shahi-template/v1/settings" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": {
        "enable_debug": true,
        "enable_analytics": false
    }
  }'
```

---

### POST /settings/export
Export all settings as JSON.

**Permission:** Admin

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/settings/export" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "json": "{\"plugin_name\":\"ShahiTemplate\",\"enable_debug\":false,...}"
    },
    "message": "Settings exported successfully"
}
```

---

### POST /settings/import
Import settings from JSON.

**Permission:** Admin

**Parameters:**
- `settings` (string, required): JSON string of settings

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/settings/import" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": "{\"plugin_name\":\"ShahiTemplate\",\"enable_debug\":false,...}"
  }'
```

---

## Onboarding Endpoints

### GET /onboarding/status
Get onboarding completion status.

**Permission:** Admin

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/onboarding/status" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "completed": false,
        "current_step": 3,
        "steps_completed": [1, 2],
        "dismissed": false
    },
    "message": "Onboarding status retrieved successfully"
}
```

---

### POST /onboarding/complete
Mark an onboarding step as complete.

**Permission:** Admin

**Parameters:**
- `step` (integer, required): Step number (1-5)

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/onboarding/complete" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"step": 3}'
```

---

### POST /onboarding/reset
Reset onboarding to start over.

**Permission:** Admin

**Example Request:**
```bash
curl -X POST "https://your-site.com/wp-json/shahi-template/v1/onboarding/reset" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

---

## System Endpoints

### GET /system/status
Get system health check and status.

**Permission:** Admin

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/system/status" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "healthy": true,
        "checks": {
            "database": {"status": "ok", "message": "Database connection OK"},
            "tables": {"status": "ok", "message": "All tables exist"},
            "php_version": {"status": "ok", "message": "PHP 8.1.0"},
            "wp_version": {"status": "ok", "message": "WordPress 6.4.0"},
            "memory": {"status": "ok", "message": "Memory limit: 256M"}
        }
    },
    "message": "System status retrieved successfully"
}
```

---

### GET /system/info
Get plugin information and available endpoints.

**Permission:** Admin

**Example Request:**
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/system/info" \
  -H "Authorization: Basic YOUR_AUTH_TOKEN"
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "name": "ShahiTemplate",
        "version": "1.0.0",
        "author": "ShahiTemplate Team",
        "php_version": "8.1.0",
        "wp_version": "6.4.0",
        "api_version": "v1",
        "endpoints": {
            "analytics": [...],
            "modules": [...],
            "settings": [...],
            "onboarding": [...],
            "system": [...]
        }
    },
    "message": "Plugin info retrieved successfully"
}
```

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 500 | Internal Server Error - Server error |

---

## Rate Limiting

Rate limiting can be enabled in plugin settings. Default limits:
- 100 requests per minute per user
- Configurable via settings

---

## Code Examples

### JavaScript (Fetch API)
```javascript
fetch('https://your-site.com/wp-json/shahi-template/v1/modules', {
    method: 'GET',
    headers: {
        'Authorization': 'Basic ' + btoa('username:password'),
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

### PHP (WordPress HTTP API)
```php
$response = wp_remote_get('https://your-site.com/wp-json/shahi-template/v1/modules', array(
    'headers' => array(
        'Authorization' => 'Basic ' . base64_encode('username:password')
    )
));

if (!is_wp_error($response)) {
    $data = json_decode(wp_remote_retrieve_body($response), true);
}
```

### cURL
```bash
curl -X GET "https://your-site.com/wp-json/shahi-template/v1/modules" \
  -H "Authorization: Basic $(echo -n 'username:password' | base64)"
```

---

## Notes

- All timestamps are in MySQL format (`Y-m-d H:i:s`)
- All data is sanitized on input and escaped on output
- Nonce verification is handled by WordPress REST API
- Cross-Origin Resource Sharing (CORS) follows WordPress defaults

---

**Last Updated:** December 14, 2025  
**API Version:** 1.0.0
