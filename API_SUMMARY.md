# 📋 ملخص Gym System APIs

## 🔐 Authentication APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | Dashboard login (Admin) | ❌ |
| POST | `/api/mobile/login` | Mobile login (Coach/Member) | ❌ |
| POST | `/api/register` | Register new user | ❌ |
| POST | `/api/logout` | Logout user | ✅ |

## 👤 Profile APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/profile` | Get user profile | ✅ |
| PUT | `/api/profile` | Update user profile | ✅ |

## 📊 Dashboard APIs (Admin Only)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/dashboard/overview` | Dashboard overview | ✅ Admin |
| GET | `/api/dashboard/stats` | Dashboard stats | ✅ Admin |
| GET | `/api/dashboard/members-stats` | Members statistics | ✅ Admin |
| GET | `/api/dashboard/coaches-stats` | Coaches statistics | ✅ Admin |
| GET | `/api/dashboard/revenue-stats` | Revenue statistics | ✅ Admin |

## 👥 User Management APIs (Admin Only)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/users` | Get all users | ✅ Admin |
| POST | `/api/users` | Create new user | ✅ Admin |
| GET | `/api/users/{id}` | Get user by ID | ✅ Admin |
| PUT | `/api/users/{id}` | Update user | ✅ Admin |
| DELETE | `/api/users/{id}` | Delete user | ✅ Admin |
| GET | `/api/users/coaches/list` | Get coaches list | ✅ Admin |
| GET | `/api/users/members/list` | Get members list | ✅ Admin |

## 🏋️ Workout Plans APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/workout-plans` | Get all plans | ✅ Admin |
| POST | `/api/workout-plans` | Create plan | ✅ Admin |
| GET | `/api/workout-plans/{id}` | Get plan by ID | ✅ Admin |
| PUT | `/api/workout-plans/{id}` | Update plan | ✅ Admin |
| DELETE | `/api/workout-plans/{id}` | Delete plan | ✅ Admin |
| GET | `/api/workout-plans/member/{id}` | Get member plans | ✅ Admin |

## 🥗 Nutrition Plans APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/nutrition-plans` | Get all plans | ✅ Admin |
| POST | `/api/nutrition-plans` | Create plan | ✅ Admin |
| GET | `/api/nutrition-plans/{id}` | Get plan by ID | ✅ Admin |
| PUT | `/api/nutrition-plans/{id}` | Update plan | ✅ Admin |
| DELETE | `/api/nutrition-plans/{id}` | Delete plan | ✅ Admin |
| GET | `/api/nutrition-plans/member/{id}` | Get member plans | ✅ Admin |

## 💳 Subscriptions APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/subscriptions` | Get all subscriptions | ✅ Admin |
| POST | `/api/subscriptions` | Create subscription | ✅ Admin |
| GET | `/api/subscriptions/{id}` | Get subscription by ID | ✅ Admin |
| PUT | `/api/subscriptions/{id}` | Update subscription | ✅ Admin |
| DELETE | `/api/subscriptions/{id}` | Delete subscription | ✅ Admin |

## 📱 Coach Mobile APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/coach/my-members` | Get my members | ✅ Coach |
| GET | `/api/coach/my-stats` | Get my stats | ✅ Coach |
| GET | `/api/coach/member/{id}` | Get member details | ✅ Coach |
| GET | `/api/coach/my-workout-plans` | Get my workout plans | ✅ Coach |
| GET | `/api/coach/my-nutrition-plans` | Get my nutrition plans | ✅ Coach |
| POST | `/api/coach/workout-plan` | Create workout plan | ✅ Coach |
| PUT | `/api/coach/workout-plan/{id}` | Update workout plan | ✅ Coach |
| DELETE | `/api/coach/workout-plan/{id}` | Delete workout plan | ✅ Coach |

## 📱 Member Mobile APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/member/my-coach` | Get my coach | ✅ Member |
| GET | `/api/member/profile` | Get my profile | ✅ Member |
| GET | `/api/member/my-workout-plans` | Get my workout plans | ✅ Member |
| GET | `/api/member/my-nutrition-plans` | Get my nutrition plans | ✅ Member |
| GET | `/api/member/my-subscription` | Get my subscription | ✅ Member |
| GET | `/api/member/my-attendance` | Get my attendance | ✅ Member |
| POST | `/api/member/check-in` | Check in | ✅ Member |
| POST | `/api/member/check-out` | Check out | ✅ Member |

## 🧪 Test APIs
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/test` | Test API connection | ❌ |
| GET | `/api/dashboard/stats/test` | Test dashboard stats | ❌ |

---

## 🔑 Authentication Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

## 📱 User Roles
- **Admin**: Dashboard management, user management, all CRUD operations
- **Coach**: Member management, workout/nutrition plans, mobile app access
- **Member**: Personal data, plans, attendance, mobile app access

## 🚀 Base URL
```
http://127.0.0.1:8000
```
