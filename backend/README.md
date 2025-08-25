# Gym System Backend

## نظرة عامة
نظام إدارة النادي الرياضي - الخلفية (Backend) مبني على Laravel

## المتطلبات
- PHP 8.1+
- Composer
- MySQL/MariaDB
- Laravel 10+

## التثبيت والإعداد

### 1. تثبيت التبعيات
```bash
composer install
```

### 2. إعداد قاعدة البيانات
```bash
# نسخ ملف البيئة
cp .env.example .env

# تعديل إعدادات قاعدة البيانات في .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gym_system
DB_USERNAME=root
DB_PASSWORD=
```

### 3. إنشاء مفتاح التطبيق
```bash
php artisan key:generate
```

### 4. تشغيل Migrations
```bash
php artisan migrate
```

### 5. تشغيل Seeders
```bash
php artisan db:seed
```

### 6. تشغيل الخادم
```bash
php artisan serve
```

## بيانات تسجيل الدخول الافتراضية

### المدير (Admin)
- **البريد الإلكتروني:** admin@gym.com
- **كلمة المرور:** password123
- **الصلاحية:** إدارة كاملة للنظام

### المدرب (Coach)
- **البريد الإلكتروني:** coach1@gym.com
- **كلمة المرور:** password123
- **الصلاحية:** إدارة الأعضاء والجلسات

### العضو (Member)
- **البريد الإلكتروني:** member1@gym.com
- **كلمة المرور:** password123
- **الصلاحية:** عرض البيانات الشخصية والخطط

## API Endpoints

### المصادقة (Authentication)
- `POST /api/login` - تسجيل الدخول للمدير
- `POST /api/mobile-login` - تسجيل الدخول للمدربين والأعضاء
- `POST /api/register` - تسجيل عضو جديد
- `POST /api/logout` - تسجيل الخروج
- `GET /api/profile` - عرض الملف الشخصي
- `PUT /api/profile` - تحديث الملف الشخصي

### المستخدمين (Users)
- `GET /api/users` - قائمة المستخدمين (Admin only)
- `GET /api/users/{id}` - عرض مستخدم محدد
- `POST /api/users` - إنشاء مستخدم جديد
- `PUT /api/users/{id}` - تحديث مستخدم
- `DELETE /api/users/{id}` - حذف مستخدم

### الأعضاء (Members)
- `GET /api/members` - قائمة الأعضاء
- `GET /api/members/{id}` - عرض عضو محدد
- `POST /api/members` - إنشاء عضو جديد
- `PUT /api/members/{id}` - تحديث عضو
- `DELETE /api/members/{id}` - حذف عضو

### المدربين (Coaches)
- `GET /api/coaches` - قائمة المدربين
- `GET /api/coaches/{id}` - عرض مدرب محدد
- `POST /api/coaches` - إنشاء مدرب جديد
- `PUT /api/coaches/{id}` - تحديث مدرب
- `DELETE /api/coaches/{id}` - حذف مدرب

### الاشتراكات (Subscriptions)
- `GET /api/subscriptions` - قائمة الاشتراكات
- `GET /api/subscriptions/{id}` - عرض اشتراك محدد
- `POST /api/subscriptions` - إنشاء اشتراك جديد
- `PUT /api/subscriptions/{id}` - تحديث اشتراك
- `DELETE /api/subscriptions/{id}` - حذف اشتراك

### خطط العضوية (Memberships)
- `GET /api/memberships` - قائمة خطط العضوية
- `GET /api/memberships/{id}` - عرض خطة عضوية محددة
- `POST /api/memberships` - إنشاء خطة عضوية جديدة
- `PUT /api/memberships/{id}` - تحديث خطة عضوية
- `DELETE /api/memberships/{id}` - حذف خطة عضوية

### البيانات الرياضية (Fitness Data)
- `GET /api/fitness-data` - قائمة البيانات الرياضية
- `GET /api/fitness-data/{id}` - عرض بيانات رياضية محددة
- `POST /api/fitness-data` - إنشاء بيانات رياضية جديدة
- `PUT /api/fitness-data/{id}` - تحديث بيانات رياضية
- `DELETE /api/fitness-data/{id}` - حذف بيانات رياضية

### الأهداف (Goals)
- `GET /api/goals` - قائمة الأهداف
- `GET /api/goals/{id}` - عرض هدف محدد
- `POST /api/goals` - إنشاء هدف جديد
- `PUT /api/goals/{id}` - تحديث هدف
- `DELETE /api/goals/{id}` - حذف هدف

### الإنجازات (Achievements)
- `GET /api/achievements` - قائمة الإنجازات
- `GET /api/achievements/{id}` - عرض إنجاز محدد
- `POST /api/achievements` - إنشاء إنجاز جديد
- `PUT /api/achievements/{id}` - تحديث إنجاز
- `DELETE /api/achievements/{id}` - حذف إنجاز

### الجلسات (Sessions)
- `GET /api/sessions` - قائمة الجلسات
- `GET /api/sessions/{id}` - عرض جلسة محددة
- `POST /api/sessions` - إنشاء جلسة جديدة
- `PUT /api/sessions/{id}` - تحديث جلسة
- `DELETE /api/sessions/{id}` - حذف جلسة

### المدفوعات (Payments)
- `GET /api/payments` - قائمة المدفوعات
- `GET /api/payments/{id}` - عرض دفعة محددة
- `POST /api/payments` - إنشاء دفعة جديدة
- `PUT /api/payments/{id}` - تحديث دفعة
- `DELETE /api/payments/{id}` - حذف دفعة

### لوحة التحكم (Dashboard)
- `GET /api/dashboard/stats` - إحصائيات عامة
- `GET /api/dashboard/members-growth` - نمو الأعضاء
- `GET /api/dashboard/revenue` - الإيرادات
- `GET /api/dashboard/attendance` - الحضور

## الأدوار والصلاحيات

### المدير (Admin)
- إدارة كاملة للنظام
- إنشاء وتعديل وحذف المستخدمين
- إدارة خطط العضوية والاشتراكات
- عرض التقارير والإحصائيات

### المدرب (Coach)
- إدارة الأعضاء المخصصين له
- إنشاء وتعديل الجلسات
- تسجيل البيانات الرياضية
- متابعة الأهداف والإنجازات

### العضو (Member)
- عرض الملف الشخصي
- متابعة الاشتراكات
- عرض الجلسات المخصصة
- متابعة التقدم والأهداف

## إعدادات CORS
تم تكوين CORS للسماح بالوصول من:
- http://localhost:3000
- http://localhost:5173
- http://localhost:5174
- http://127.0.0.1:3000
- http://127.0.0.1:5173
- http://127.0.0.1:5174

## استكشاف الأخطاء

### مشكلة "فشل في جلب البيانات من قاعدة البيانات"
1. تأكد من تشغيل الخادم: `php artisan serve`
2. تأكد من وجود بيانات في قاعدة البيانات: `php artisan tinker`
3. تأكد من إعدادات CORS في `config/cors.php`
4. تحقق من سجلات الأخطاء: `storage/logs/laravel.log`

### مشكلة "Class not found"
1. حدث autoloader: `composer dump-autoload`
2. امسح الكاش: `php artisan cache:clear`
3. امسح config: `php artisan config:clear`

### مشكلة الاتصال بقاعدة البيانات
1. تحقق من إعدادات `.env`
2. تأكد من تشغيل MySQL
3. تحقق من وجود قاعدة البيانات

## الدعم
للمساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.
