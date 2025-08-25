import React from 'react'
import { CogIcon, UserIcon, ShieldCheckIcon, BellIcon } from '@heroicons/react/24/outline'

const Settings: React.FC = () => {
  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">الإعدادات</h1>
          <p className="text-gray-600 mt-2">إعدادات النظام والحساب</p>
        </div>
      </div>

      {/* Settings Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Profile Settings */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <UserIcon className="h-8 w-8 text-blue-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">إعدادات الملف الشخصي</h3>
          </div>
          <p className="text-gray-600 mb-4">تعديل المعلومات الشخصية وكلمة المرور</p>
          <button className="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            تعديل
          </button>
        </div>

        {/* Security Settings */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <ShieldCheckIcon className="h-8 w-8 text-green-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">إعدادات الأمان</h3>
          </div>
          <p className="text-gray-600 mb-4">إعدادات المصادقة والأمان</p>
          <button className="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            تعديل
          </button>
        </div>

        {/* Notification Settings */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <BellIcon className="h-8 w-8 text-purple-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">إعدادات الإشعارات</h3>
          </div>
          <p className="text-gray-600 mb-4">تخصيص الإشعارات والتنبيهات</p>
          <button className="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            تعديل
          </button>
        </div>

        {/* System Settings */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <CogIcon className="h-8 w-8 text-orange-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">إعدادات النظام</h3>
          </div>
          <p className="text-gray-600 mb-4">إعدادات النظام والتكوين</p>
          <button className="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
            تعديل
          </button>
        </div>
      </div>

      {/* Placeholder Content */}
      <div className="bg-white rounded-lg shadow p-8 text-center">
        <CogIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-medium text-gray-900 mb-2">صفحة الإعدادات قيد التطوير</h3>
        <p className="text-gray-600">
          سيتم إضافة خيارات الإعدادات قريباً
        </p>
      </div>
    </div>
  )
}

export default Settings
