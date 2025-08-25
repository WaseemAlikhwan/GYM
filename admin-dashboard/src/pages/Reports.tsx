import React from 'react'
import { ChartBarIcon, DocumentTextIcon, TableCellsIcon } from '@heroicons/react/24/outline'

const Reports: React.FC = () => {
  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">التقارير</h1>
          <p className="text-gray-600 mt-2">تقارير شاملة عن أداء النادي الرياضي</p>
        </div>
      </div>

      {/* Reports Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Membership Report */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <ChartBarIcon className="h-8 w-8 text-blue-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">تقرير العضويات</h3>
          </div>
          <p className="text-gray-600 mb-4">تحليل شامل لخطط العضوية والاشتراكات</p>
          <button className="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            عرض التقرير
          </button>
        </div>

        {/* Attendance Report */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <DocumentTextIcon className="h-8 w-8 text-green-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">تقرير الحضور</h3>
          </div>
          <p className="text-gray-600 mb-4">إحصائيات الحضور والغياب للأعضاء</p>
          <button className="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            عرض التقرير
          </button>
        </div>

        {/* Financial Report */}
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div className="flex items-center mb-4">
            <TableCellsIcon className="h-8 w-8 text-purple-600 mr-3" />
            <h3 className="text-lg font-medium text-gray-900">التقرير المالي</h3>
          </div>
          <p className="text-gray-600 mb-4">تحليل الإيرادات والمصروفات</p>
          <button className="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            عرض التقرير
          </button>
        </div>
      </div>

      {/* Placeholder Content */}
      <div className="bg-white rounded-lg shadow p-8 text-center">
        <ChartBarIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-medium text-gray-900 mb-2">صفحة التقارير قيد التطوير</h3>
        <p className="text-gray-600">
          سيتم إضافة تقارير تفصيلية قريباً
        </p>
      </div>
    </div>
  )
}

export default Reports
