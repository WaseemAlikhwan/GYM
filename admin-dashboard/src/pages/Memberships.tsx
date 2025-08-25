import React, { useState, useEffect } from 'react'
import membershipApi, { Membership, MembershipFormData, MembershipStats } from '../services/membershipApi'
import { PlusIcon, PencilIcon, TrashIcon, EyeIcon, StarIcon } from '@heroicons/react/24/outline'

const Memberships: React.FC = () => {
  const [memberships, setMemberships] = useState<Membership[]>([])
  const [stats, setStats] = useState<MembershipStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [showEditModal, setShowEditModal] = useState(false)
  const [selectedMembership, setSelectedMembership] = useState<Membership | null>(null)
  const [filters, setFilters] = useState({
    status: '',
    price_range: ''
  })

  // Form state
  const [formData, setFormData] = useState<MembershipFormData>({
    name: '',
    description: '',
    price: 0,
    duration_days: 30,
    has_coach: false,
    has_workout_plan: false,
    has_nutrition_plan: false,
    is_active: true
  })

  useEffect(() => {
    fetchData()
  }, [filters])

  const fetchData = async () => {
    try {
      setLoading(true)
      setError(null)
      
      const [membershipsRes, statsRes] = await Promise.all([
        membershipApi.getMemberships(),
        membershipApi.getMembershipStats()
      ])
      
      setMemberships(membershipsRes)
      setStats(statsRes)
      
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleCreateMembership = async () => {
    try {
      setLoading(true)
      const newMembership = await membershipApi.createMembership(formData)
      setShowCreateModal(false)
      resetForm()
      
      // رسالة نجاح
      alert(`تم إنشاء خطة العضوية "${newMembership.name}" بنجاح`)
      
      fetchData()
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleUpdateMembership = async () => {
    if (!selectedMembership) return
    
    try {
      setLoading(true)
      const updatedMembership = await membershipApi.updateMembership(selectedMembership.id, formData)
      setShowEditModal(false)
      setSelectedMembership(null)
      resetForm()
      
      // رسالة نجاح
      alert(`تم تحديث خطة العضوية "${updatedMembership.name}" بنجاح`)
      
      fetchData()
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteMembership = async (id: number) => {
    const membership = memberships.find(m => m.id === id)
    if (!membership) return
    
    if (!confirm(`هل أنت متأكد من حذف خطة العضوية "${membership.name}"؟\n\nهذا الإجراء لا يمكن التراجع عنه.`)) return
    
    try {
      setLoading(true)
      await membershipApi.deleteMembership(id)
      
      // رسالة نجاح
      alert(`تم حذف خطة العضوية "${membership.name}" بنجاح`)
      
      // إعادة تحميل البيانات
      fetchData()
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      description: '',
      price: 0,
      duration_days: 30,
      has_coach: false,
      has_workout_plan: false,
      has_nutrition_plan: false,
      is_active: true
    })
  }

  const openEditModal = (membership: Membership) => {
    setSelectedMembership(membership)
    setFormData({
      name: membership.name,
      description: membership.description,
      price: membership.price,
      duration_days: membership.duration_days,
      has_coach: membership.has_coach,
      has_workout_plan: membership.has_workout_plan,
      has_nutrition_plan: membership.has_nutrition_plan,
      is_active: membership.is_active
    })
    setShowEditModal(true)
  }

  const getStatusBadge = (membership: Membership) => {
    if (membership.is_active) {
      return <span className="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">نشط</span>
    } else {
      return <span className="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">غير نشط</span>
    }
  }

  const getFeaturesBadges = (membership: Membership) => {
    const badges = []
    
    if (membership.has_coach) {
      badges.push(<span key="coach" className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full mr-1">مدرب</span>)
    }
    
    if (membership.has_workout_plan) {
      badges.push(<span key="workout" className="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full mr-1">خطة تمارين</span>)
    }
    
    if (membership.has_nutrition_plan) {
      badges.push(<span key="nutrition" className="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full mr-1">خطة تغذية</span>)
    }
    
    return badges
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">جاري تحميل البيانات...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="flex">
          <div className="text-red-800">
            <p className="font-medium">خطأ في تحميل البيانات</p>
            <div className="text-sm mt-2">{error}</div>
            <button 
              onClick={fetchData}
              className="mt-4 text-sm text-red-600 hover:text-red-800 underline"
            >
              حاول مرة أخرى
            </button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">إدارة خطط العضوية</h1>
          <p className="text-gray-600 mt-2">إدارة خطط العضوية المتاحة في النادي الرياضي</p>
        </div>
        <div className="flex gap-2">
          <button 
            onClick={() => {
              if (confirm('هل تريد إعادة تحميل البيانات من قاعدة البيانات؟')) {
                // إعادة تعيين البيانات المحلية
                window.location.reload()
              }
            }}
            className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center gap-2"
          >
            <StarIcon className="h-5 w-5" />
            إعادة تعيين البيانات
          </button>
          <button
            onClick={() => setShowCreateModal(true)}
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <PlusIcon className="h-5 w-5" />
            إضافة خطة عضوية جديدة
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-blue-600">{stats.total_memberships}</div>
            <div className="text-sm text-gray-600">إجمالي خطط العضوية</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-green-600">{stats.active_memberships}</div>
            <div className="text-sm text-gray-600">الخطط النشطة</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-purple-600">{stats.total_subscriptions}</div>
            <div className="text-sm text-gray-600">إجمالي الاشتراكات</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-orange-600">{stats.active_subscriptions}</div>
            <div className="text-sm text-gray-600">الاشتراكات النشطة</div>
          </div>
        </div>
      )}

      {/* Revenue Chart */}
      {stats && stats.revenue_by_membership.length > 0 && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">الإيرادات حسب خطة العضوية</h3>
          <div className="space-y-3">
            {stats.revenue_by_membership.map((item, index) => (
              <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center">
                  <div className="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                  <span className="font-medium text-gray-900">{item.membership_name}</span>
                </div>
                <div className="text-right">
                  <div className="text-sm text-gray-600">{item.subscriptions_count} اشتراك</div>
                  <div className="font-medium text-green-600">${item.total_revenue}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
            <select
              value={filters.status}
              onChange={(e) => setFilters({ ...filters, status: e.target.value })}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">جميع الحالات</option>
              <option value="active">نشط</option>
              <option value="inactive">غير نشط</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">نطاق السعر</label>
            <select
              value={filters.price_range}
              onChange={(e) => setFilters({ ...filters, price_range: e.target.value })}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">جميع الأسعار</option>
              <option value="0-50">$0 - $50</option>
              <option value="51-100">$51 - $100</option>
              <option value="101-200">$101 - $200</option>
              <option value="201+">$201+</option>
            </select>
          </div>
        </div>
      </div>

      {/* Memberships Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">قائمة خطط العضوية</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  اسم الخطة
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الوصف
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  السعر
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  المدة
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الميزات
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الحالة
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {memberships.length === 0 ? (
                <tr>
                  <td colSpan={7} className="px-6 py-4 text-center text-gray-500">
                    لا توجد خطط عضوية
                  </td>
                </tr>
              ) : (
                memberships.map((membership) => (
                  <tr key={membership.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <StarIcon className="h-5 w-5 text-yellow-400 mr-2" />
                        <div className="text-sm font-medium text-gray-900">{membership.name}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-sm text-gray-900 max-w-xs truncate">
                        {membership.description}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-green-600">${membership.price}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {membership.duration_days} يوم
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex flex-wrap gap-1">
                        {getFeaturesBadges(membership)}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {getStatusBadge(membership)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex gap-2">
                        <button
                          onClick={() => openEditModal(membership)}
                          className="text-blue-600 hover:text-blue-900"
                          title="تعديل"
                        >
                          <PencilIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleDeleteMembership(membership.id)}
                          className="text-red-600 hover:text-red-900"
                          title="حذف"
                        >
                          <TrashIcon className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create/Edit Modal */}
      {(showCreateModal || showEditModal) && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                {showCreateModal ? 'إضافة خطة عضوية جديدة' : 'تعديل خطة العضوية'}
              </h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">اسم الخطة</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="مثال: Basic, Premium, VIP"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                  <textarea
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    rows={3}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="وصف الخطة والميزات"
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">السعر</label>
                    <input
                      type="number"
                      value={formData.price}
                      onChange={(e) => setFormData({ ...formData, price: Number(e.target.value) })}
                      className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="0"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">المدة (أيام)</label>
                    <input
                      type="number"
                      value={formData.duration_days}
                      onChange={(e) => setFormData({ ...formData, duration_days: Number(e.target.value) })}
                      className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="30"
                    />
                  </div>
                </div>
                
                <div className="space-y-3">
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      checked={formData.has_coach}
                      onChange={(e) => setFormData({ ...formData, has_coach: e.target.checked })}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span className="ml-2 text-sm text-gray-700">يشمل مدرب شخصي</span>
                  </label>
                  
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      checked={formData.has_workout_plan}
                      onChange={(e) => setFormData({ ...formData, has_workout_plan: e.target.checked })}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span className="ml-2 text-sm text-gray-700">يشمل خطة تمارين</span>
                  </label>
                  
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      checked={formData.has_nutrition_plan}
                      onChange={(e) => setFormData({ ...formData, has_nutrition_plan: e.target.checked })}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span className="ml-2 text-sm text-gray-700">يشمل خطة تغذية</span>
                  </label>
                  
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      checked={formData.is_active}
                      onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span className="ml-2 text-sm text-gray-700">نشط</span>
                  </label>
                </div>
              </div>
              
              <div className="flex gap-3 mt-6">
                <button
                  onClick={showCreateModal ? handleCreateMembership : handleUpdateMembership}
                  className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                >
                  {showCreateModal ? 'إنشاء' : 'تحديث'}
                </button>
                <button
                  onClick={() => {
                    setShowCreateModal(false)
                    setShowEditModal(false)
                    resetForm()
                  }}
                  className="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
                >
                  إلغاء
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Memberships
