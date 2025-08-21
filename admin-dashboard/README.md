# Gym Admin Dashboard

لوحة تحكم متصلة مع Laravel Backend مع نظام مصادقة كامل

## 🚀 التشغيل

```bash
npm install
npm run dev
```

المشروع سيعمل على: http://localhost:3000

## 🔐 **نظام المصادقة**

### 1. **صفحة Login**
- مسار: `/login`
- تصميم جميل ومتجاوب
- معالجة الأخطاء
- حفظ المسار السابق للعودة إليه

### 2. **المصادقة المحمية**
- جميع الصفحات محمية (Dashboard, Members, إلخ)
- التحقق التلقائي من صحة التوكن
- إعادة التوجيه التلقائي للـ Login

### 3. **إدارة الجلسة**
- حفظ التوكن في localStorage
- تحديث تلقائي للملف الشخصي
- تسجيل الخروج مع تنظيف البيانات

## 🔗 **ربط مع Laravel Backend**

### 1. **تأكد من تشغيل Laravel Backend**
```bash
cd backend
php artisan serve
```
Backend سيعمل على: http://localhost:8000

### 2. **API Endpoints المطلوبة**
يحتاج النظام إلى هذه الـ endpoints:

#### **المصادقة:**
- `POST /api/login` - تسجيل الدخول
- `POST /api/logout` - تسجيل الخروج
- `GET /api/profile` - الملف الشخصي
- `PUT /api/profile` - تحديث الملف الشخصي

#### **Dashboard:**
- `GET /api/dashboard/stats` - إحصائيات عامة
- `GET /api/dashboard/members-stats` - إحصائيات الأعضاء
- `GET /api/dashboard/coaches-stats` - إحصائيات المدربين
- `GET /api/dashboard/revenue-stats` - إحصائيات الإيرادات

### 3. **إعداد CORS في Laravel**
في ملف `backend/config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['*'],
```

## ✅ **ما تم إنجازه:**

1. ✅ **نظام مصادقة كامل** - Login, Logout, Protected Routes
2. ✅ **Auth Context** - إدارة حالة المصادقة
3. ✅ **Auth API Service** - التواصل مع Backend
4. ✅ **Protected Routes** - حماية الصفحات
5. ✅ **Dashboard متصل مع API** - يجلب البيانات من Laravel Backend
6. ✅ **API Service** - للتواصل مع Backend
7. ✅ **Error Handling** - مع fallback للبيانات التجريبية
8. ✅ **Loading States** - حالات التحميل
9. ✅ **Header** مع اسم المستخدم وزر تسجيل الخروج
10. ✅ **Sidebar** مع قائمة التنقل
11. ✅ **صفحة Members** مع جدول بيانات بسيط
12. ✅ **React Router** للتنقل بين الصفحات
13. ✅ **تخطيط متجاوب** مع Tailwind CSS

## 📋 **الخطوات التالية:**

1. 🔄 **إنشاء API Controllers** في Laravel Backend
2. 🔄 **ربط صفحة Members** مع API
3. 🔄 **إضافة المزيد من الصفحات** (Coaches, Workout Plans, إلخ)
4. 🔄 **إضافة نماذج** لإدخال البيانات
5. 🔄 **إضافة الرسوم البيانية** مع Recharts

## 🛠️ التقنيات المستخدمة

- React 18 + TypeScript
- Vite
- Tailwind CSS
- Axios (API calls)
- React Router DOM
- Context API (State Management)

## 📁 **هيكل المشروع**

```
src/
├── components/
│   ├── Header.tsx         # شريط علوي مع معلومات المستخدم
│   ├── Sidebar.tsx        # قائمة جانبية
│   ├── Layout.tsx         # تخطيط مشترك للصفحات
│   └── ProtectedRoute.tsx # حماية الصفحات
├── contexts/
│   └── AuthContext.tsx    # إدارة حالة المصادقة
├── pages/
│   ├── Login.tsx          # صفحة تسجيل الدخول
│   ├── Dashboard.tsx      # لوحة التحكم (متصل مع API)
│   └── Members.tsx        # إدارة الأعضاء
├── services/
│   ├── api.ts             # إعدادات Axios الأساسية
│   ├── authApi.ts         # API calls للمصادقة
│   └── dashboardApi.ts    # API calls للـ Dashboard
├── App.tsx                # المكون الرئيسي مع React Router
├── main.tsx               # نقطة الدخول
└── index.css              # الأنماط
```

## 🎯 **الهدف التالي**

سنقوم بإنشاء API Controllers في Laravel Backend لتوفير البيانات للـ Dashboard والمصادقة، ثم نربط صفحة Members مع API أيضاً.

## 🔧 **اختبار الاتصال**

### 1. **اختبار Login:**
- اذهب إلى http://localhost:3000/login
- استخدم بيانات تجريبية: admin@example.com / password
- سيتم توجيهك للـ Dashboard

### 2. **اختبار API:**
- تأكد من تشغيل Laravel Backend على http://localhost:8000
- Dashboard سيجلب البيانات من API
- إذا لم يكن Backend يعمل، سيعرض بيانات تجريبية

### 3. **اختبار الحماية:**
- حاول الوصول لـ http://localhost:3000/dashboard بدون تسجيل دخول
- سيتم توجيهك للـ Login تلقائياً
