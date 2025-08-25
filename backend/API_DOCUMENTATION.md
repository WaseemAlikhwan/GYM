# Gym System API Documentation

## Overview
This document provides comprehensive documentation for the Gym System API endpoints. The API is built using Laravel and follows RESTful conventions.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Response Format
All API responses follow this format:
```json
{
    "success": true/false,
    "message": "Response message",
    "data": {...}
}
```

---

## Authentication Endpoints

### Login
- **URL:** `POST /login`
- **Description:** Authenticate user and get access token
- **Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```
- **Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {...},
        "token": "access_token_here"
    }
}
```

### Register
- **URL:** `POST /register`
- **Description:** Register a new user
- **Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "member",
    "phone": "+1234567890"
}
```

### Logout
- **URL:** `POST /logout`
- **Description:** Logout user and invalidate token
- **Headers:** Authorization required

### Get Profile
- **URL:** `GET /profile`
- **Description:** Get current user profile
- **Headers:** Authorization required

### Update Profile
- **URL:** `PUT /profile`
- **Description:** Update current user profile
- **Headers:** Authorization required

---

## Dashboard Endpoints

### Dashboard Overview
- **URL:** `GET /dashboard/overview`
- **Description:** Get dashboard overview based on user role
- **Headers:** Authorization required
- **Response (Admin):**
```json
{
    "success": true,
    "data": {
        "total_members": 150,
        "total_coaches": 10,
        "active_subscriptions": 120,
        "expired_subscriptions": 5,
        "subscriptions_expiring_soon": 8,
        "new_members_this_month": 15,
        "total_attendance_today": 45,
        "total_revenue": 15000,
        "gym_status": {...},
        "recent_activities": {...},
        "monthly_stats": {...}
    }
}
```

### Get Members List
- **URL:** `GET /dashboard/members`
- **Description:** Get members list with filters
- **Headers:** Authorization required
- **Query Parameters:**
  - `search` (optional): Search by name or email
  - `status` (optional): Filter by subscription status
- **Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "total": 150
    }
}
```

### Get Coaches List
- **URL:** `GET /dashboard/coaches`
- **Description:** Get coaches list (admin only)
- **Headers:** Authorization required
- **Query Parameters:**
  - `search` (optional): Search by name or email

### Attendance Statistics
- **URL:** `GET /dashboard/attendance-stats`
- **Description:** Get attendance statistics
- **Headers:** Authorization required
- **Query Parameters:**
  - `period` (optional): week, month, year (default: week)

### Subscription Statistics
- **URL:** `GET /dashboard/subscription-stats`
- **Description:** Get subscription statistics (admin only)
- **Headers:** Authorization required

### Gym Status
- **URL:** `GET /dashboard/gym-status`
- **Description:** Get gym status and logs (admin only)
- **Headers:** Authorization required

---

## User Management Endpoints

### Get All Users
- **URL:** `GET /users`
- **Description:** Get all users with filters (admin only)
- **Headers:** Authorization required
- **Query Parameters:**
  - `role` (optional): admin, coach, member
  - `search` (optional): Search by name, email, or phone
  - `status` (optional): active, inactive, suspended

### Create User
- **URL:** `POST /users`
- **Description:** Create a new user (admin only)
- **Headers:** Authorization required
- **Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "role": "member",
    "phone": "+1234567890",
    "address": "123 Main St",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "emergency_contact": "Jane Doe",
    "medical_conditions": "None",
    "fitness_goals": "Weight loss",
    "experience_level": "beginner",
    "specializations": ["weightlifting", "cardio"]
}
```

### Get User Details
- **URL:** `GET /users/{id}`
- **Description:** Get specific user details
- **Headers:** Authorization required

### Update User
- **URL:** `PUT /users/{id}`
- **Description:** Update user details
- **Headers:** Authorization required

### Delete User
- **URL:** `DELETE /users/{id}`
- **Description:** Delete user (admin only)
- **Headers:** Authorization required

### Get Coaches List
- **URL:** `GET /users/coaches/list`
- **Description:** Get available coaches
- **Headers:** Authorization required
- **Query Parameters:**
  - `search` (optional): Search by name or email
  - `specialization` (optional): Filter by specialization

### Get Members List
- **URL:** `GET /users/members/list`
- **Description:** Get members list
- **Headers:** Authorization required
- **Query Parameters:**
  - `search` (optional): Search by name or email
  - `subscription_status` (optional): active, inactive

### Get User Statistics
- **URL:** `GET /users/stats`
- **Description:** Get user statistics (admin only)
- **Headers:** Authorization required

### Update Profile
- **URL:** `PUT /users/profile/update`
- **Description:** Update own profile
- **Headers:** Authorization required

### Change Password
- **URL:** `POST /users/change-password`
- **Description:** Change user password
- **Headers:** Authorization required
- **Request Body:**
```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "confirm_password": "newpassword123"
}
```

---

## Coach-Member Relationship Endpoints

### Get Relationships
- **URL:** `GET /coach-members`
- **Description:** Get coach-member relationships
- **Headers:** Authorization required

### Assign Member to Coach
- **URL:** `POST /coach-members/assign`
- **Description:** Assign a member to a coach (admin only)
- **Headers:** Authorization required
- **Request Body:**
```json
{
    "coach_id": 1,
    "member_id": 5,
    "start_date": "2024-01-01",
    "notes": "Initial assignment"
}
```

### Update Relationship
- **URL:** `PUT /coach-members/{id}`
- **Description:** Update coach-member relationship
- **Headers:** Authorization required
- **Request Body:**
```json
{
    "status": "active",
    "notes": "Updated notes",
    "end_date": "2024-12-31"
}
```

### Delete Relationship
- **URL:** `DELETE /coach-members/{id}`
- **Description:** Remove coach-member relationship
- **Headers:** Authorization required

### Get Coach's Members
- **URL:** `GET /coach-members/coach/{coachId}/members`
- **Description:** Get members assigned to a specific coach
- **Headers:** Authorization required

### Get Member's Coach
- **URL:** `GET /coach-members/member/{memberId}/coach`
- **Description:** Get coach assigned to a specific member
- **Headers:** Authorization required

### Get Available Coaches
- **URL:** `GET /coach-members/available-coaches`
- **Description:** Get available coaches for assignment (admin only)
- **Headers:** Authorization required

### Get Unassigned Members
- **URL:** `GET /coach-members/unassigned-members`
- **Description:** Get members without coaches (admin only)
- **Headers:** Authorization required

### Bulk Assign Members
- **URL:** `POST /coach-members/bulk-assign`
- **Description:** Assign multiple members to a coach (admin only)
- **Headers:** Authorization required
- **Request Body:**
```json
{
    "coach_id": 1,
    "member_ids": [5, 6, 7, 8],
    "start_date": "2024-01-01",
    "notes": "Bulk assignment"
}
```

### Get Relationship Statistics
- **URL:** `GET /coach-members/stats`
- **Description:** Get coach-member relationship statistics
- **Headers:** Authorization required

---

## Role-Specific Endpoints

### Admin Routes

#### System Statistics
- **URL:** `GET /admin/system-stats`
- **Description:** Get comprehensive system statistics
- **Headers:** Authorization required

#### User Management
- **URL:** `GET /admin/user-management`
- **Description:** Get user management interface data
- **Headers:** Authorization required

#### Coach Management
- **URL:** `GET /admin/coach-management`
- **Description:** Get coach management interface data
- **Headers:** Authorization required

### Coach Routes

#### My Members
- **URL:** `GET /coach/my-members`
- **Description:** Get coach's assigned members
- **Headers:** Authorization required

#### My Statistics
- **URL:** `GET /coach/my-stats`
- **Description:** Get coach's personal statistics
- **Headers:** Authorization required

#### Member Details
- **URL:** `GET /coach/member/{memberId}`
- **Description:** Get specific member details
- **Headers:** Authorization required

### Member Routes

#### My Coach
- **URL:** `GET /member/my-coach`
- **Description:** Get member's assigned coach
- **Headers:** Authorization required

#### My Profile
- **URL:** `GET /member/profile`
- **Description:** Get member's own profile
- **Headers:** Authorization required

---

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "message": "Access denied"
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "User not found"
}
```

---

## Data Models

### User Model
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "member",
    "phone": "+1234567890",
    "address": "123 Main St",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "emergency_contact": "Jane Doe",
    "medical_conditions": "None",
    "fitness_goals": "Weight loss",
    "experience_level": "beginner",
    "specializations": ["weightlifting", "cardio"],
    "status": "active",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Coach-Member Relationship Model
```json
{
    "id": 1,
    "coach_id": 1,
    "member_id": 5,
    "start_date": "2024-01-01",
    "end_date": null,
    "status": "active",
    "notes": "Initial assignment",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "coach": {...},
    "member": {...}
}
```

---

## Testing the API

### Using Postman
1. Import the collection
2. Set the base URL
3. Login to get the token
4. Use the token in Authorization header for subsequent requests

### Using cURL
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use token
curl -X GET http://localhost:8000/api/dashboard/overview \
  -H "Authorization: Bearer {token}"
```

---

## Notes
- All timestamps are in ISO 8601 format
- Pagination is included for list endpoints
- Search functionality is available for most list endpoints
- Role-based access control is implemented
- All responses include success status and appropriate messages 