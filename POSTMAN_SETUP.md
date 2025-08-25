# 🚀 إعداد Postman لـ Gym System API

## 📋 المتطلبات
- Postman (أو Postman Desktop App)
- Gym System Backend يعمل على `http://127.0.0.1:8000`

## 🔧 خطوات الإعداد

### 1. استيراد Collection
1. افتح Postman
2. اضغط على **Import**
3. اختر ملف `Gym-System-API.postman_collection.json`
4. سيتم استيراد جميع الـ APIs

### 2. استيراد Environment
1. اضغط على **Import** مرة أخرى
2. اختر ملف `Gym-System-Environment.postman_environment.json`
3. اختر Environment من القائمة المنسدلة

### 3. تحديث المتغيرات
```json
{
  "base_url": "http://127.0.0.1:8000",
  "auth_token": "",
  "admin_token": "",
  "coach_token": "",
  "member_token": ""
}
```

## 🔐 كيفية الحصول على Tokens

### 1. Admin Token
```bash
POST {{base_url}}/api/login
{
  "email": "admin@example.com",
  "password": "password123"
}
```
انسخ `data.token` إلى `admin_token`

### 2. Coach Token
```bash
POST {{base_url}}/api/mobile/login
{
  "email": "coach@test.com",
  "password": "12345678"
}
```
انسخ `data.token` إلى `coach_token`

### 3. Member Token
```bash
POST {{base_url}}/api/mobile/login
{
  "email": "member@test.com",
  "password": "12345678"
}
```
انسخ `data.token` إلى `member_token`

## 📱 مجموعات الـ APIs

### 🔐 Authentication
- **Dashboard Login** - تسجيل دخول المشرف
- **Mobile Login** - تسجيل دخول المدرب/العضو
- **Register** - تسجيل عضو جديد
- **Logout** - تسجيل الخروج

### 👤 User Profile
- **Get Profile** - الحصول على الملف الشخصي
- **Update Profile** - تحديث الملف الشخصي

### 📊 Dashboard (Admin Only)
- **Overview** - نظرة عامة على النظام
- **Stats** - إحصائيات شاملة
- **Members Stats** - إحصائيات الأعضاء
- **Coaches Stats** - إحصائيات المدربين
- **Revenue Stats** - إحصائيات الإيرادات

### 👥 User Management (Admin Only)
- **Get All Users** - جلب جميع المستخدمين
- **Get Coaches List** - قائمة المدربين
- **Get Members List** - قائمة الأعضاء
- **Create User** - إنشاء مستخدم جديد
- **Update User** - تحديث المستخدم
- **Delete User** - حذف المستخدم

### 🏋️ Workout Plans
- **Get All Plans** - جلب جميع خطط التمارين
- **Create Plan** - إنشاء خطة تمارين
- **Get Member Plans** - خطط عضو معين

### 🥗 Nutrition Plans
- **Get All Plans** - جلب جميع خطط التغذية
- **Create Plan** - إنشاء خطة تغذية

### 💳 Subscriptions
- **Get All** - جلب جميع الاشتراكات
- **Create** - إنشاء اشتراك جديد

### 📱 Coach Mobile App
- **My Members** - أعضائي
- **My Stats** - إحصائياتي
- **Member Details** - تفاصيل العضو
- **Create Workout Plan** - إنشاء خطة تمارين

### 📱 Member Mobile App
- **My Coach** - مدربي
- **My Workout Plans** - خطط تماريني
- **My Nutrition Plans** - خطط تغذيتي
- **My Subscription** - اشتراكي
- **Check In/Out** - تسجيل الحضور/الانصراف

## 🧪 Test Routes
- **Test API** - اختبار الاتصال
- **Test Dashboard Stats** - اختبار إحصائيات Dashboard

## 💡 نصائح للاستخدام

### 1. ترتيب الاختبار
1. ابدأ بـ **Test API** للتأكد من عمل الخادم
2. اختبر **Authentication** للحصول على Tokens
3. اختبر **Dashboard APIs** (Admin)
4. اختبر **Mobile APIs** (Coach/Member)

### 2. إدارة Tokens
- احفظ كل Token في المتغير المناسب
- استخدم `{{admin_token}}` للمسارات الإدارية
- استخدم `{{coach_token}}` لمسارات المدرب
- استخدم `{{member_token}}` لمسارات العضو

### 3. اختبار الأدوار
- تأكد من أن كل دور يمكنه الوصول لمساراته فقط
- اختبر رفض الوصول للمسارات غير المصرح بها

## 🚨 استكشاف الأخطاء

### مشكلة: 401 Unauthorized
- تأكد من صحة Token
- تأكد من إرسال `Authorization: Bearer {token}`

### مشكلة: 403 Forbidden
- تأكد من أن المستخدم لديه الصلاحية المناسبة
- تأكد من استخدام Token الدور الصحيح

### مشكلة: 500 Internal Server Error
- تحقق من سجلات الخادم
- تأكد من صحة البيانات المرسلة

## 📞 الدعم
إذا واجهت أي مشاكل، تحقق من:
1. أن الخادم يعمل على `http://127.0.0.1:8000`
2. أن قاعدة البيانات متصلة
3. أن جميع الجداول موجودة
4. أن المستخدمين موجودون في قاعدة البيانات

---

**🎯 الآن يمكنك اختبار جميع APIs بسهولة باستخدام Postman!**
