import React, { useState, useEffect } from 'react'
import subscriptionApi, { Subscription, SubscriptionFormData, SubscriptionStats } from '../services/subscriptionApi'
import { PlusIcon, PencilIcon, TrashIcon, EyeIcon, ArrowPathIcon } from '@heroicons/react/24/outline'

const Subscriptions: React.FC = () => {
  const [subscriptions, setSubscriptions] = useState<Subscription[]>([])
  const [stats, setStats] = useState<SubscriptionStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [showEditModal, setShowEditModal] = useState(false)
  const [selectedSubscription, setSelectedSubscription] = useState<Subscription | null>(null)
  const [memberships, setMemberships] = useState<any[]>([])
  const [users, setUsers] = useState<any[]>([])
  const [filters, setFilters] = useState({
    status: '',
    membership_id: ''
  })

  // Form state
  const [formData, setFormData] = useState<SubscriptionFormData>({
    user_id: 0,
    membership_id: 0,
    start_date: '',
    end_date: '',
    notes: ''
  })

  useEffect(() => {
    fetchData()
  }, [filters])

  const fetchData = async () => {
    try {
      setLoading(true)
      setError(null)
      
      const [subscriptionsRes, statsRes, membershipsRes] = await Promise.all([
        subscriptionApi.getSubscriptions(filters),
        subscriptionApi.getSubscriptionStats(),
        subscriptionApi.getMemberships()
      ])
      
      setSubscriptions(subscriptionsRes.data)
      setStats(statsRes)
      setMemberships(membershipsRes)
      
      // Fetch users for the form
      // TODO: Create users API service
      // const usersRes = await usersApi.getUsers({ role: 'member' })
      // setUsers(usersRes.data)
      
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleCreateSubscription = async () => {
    try {
      await subscriptionApi.createSubscription(formData)
      setShowCreateModal(false)
      resetForm()
      fetchData()
    } catch (err: any) {
      setError(err.message)
    }
  }

  const handleUpdateSubscription = async () => {
    if (!selectedSubscription) return
    
    try {
      await subscriptionApi.updateSubscription(selectedSubscription.id, formData)
      setShowEditModal(false)
      setSelectedSubscription(null)
      resetForm()
      fetchData()
    } catch (err: any) {
      setError(err.message)
    }
  }

  const handleDeleteSubscription = async (id: number) => {
    if (!confirm('هل أنت متأكد من حذف هذا الاشتراك؟')) return
    
    try {
      await subscriptionApi.deleteSubscription(id)
      fetchData()
    } catch (err: any) {
      setError(err.message)
    }
  }

  const handleRenewSubscription = async (id: number) => {
    const newEndDate = prompt('أدخل تاريخ انتهاء جديد (YYYY-MM-DD):')
    if (!newEndDate) return
    
    try {
      await subscriptionApi.renewSubscription(id, { end_date: newEndDate })
      fetchData()
    } catch (err: any) {
      setError(err.message)
    }
  }

  const handleCancelSubscription = async (id: number) => {
    if (!confirm('هل أنت متأكد من إلغاء هذا الاشتراك؟')) return
    
    try {
      await subscriptionApi.cancelSubscription(id)
      fetchData()
    } catch (err: any) {
      setError(err.message)
    }
  }

  const resetForm = () => {
    setFormData({
      user_id: 0,
      membership_id: 0,
      start_date: '',
      end_date: '',
      notes: ''
    })
  }

  const openEditModal = (subscription: Subscription) => {
    setSelectedSubscription(subscription)
    setFormData({
      user_id: subscription.user_id,
      membership_id: subscription.membership_id,
      start_date: subscription.start_date,
      end_date: subscription.end_date,
      notes: subscription.notes || ''
    })
    setShowEditModal(true)
  }

  const getStatusBadge = (subscription: Subscription) => {
    const endDate = new Date(subscription.end_date)
    const today = new Date()
    
    if (endDate < today) {
      return <span className="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">منتهي</span>
    } else if (endDate.getTime() - today.getTime() < 7 * 24 * 60 * 60 * 1000) {
      return <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">ينتهي قريباً</span>
    } else {
      return <span className="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">نشط</span>
    }
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
          <h1 className="text-3xl font-bold text-gray-900">إدارة الاشتراكات</h1>
          <p className="text-gray-600 mt-2">إدارة اشتراكات الأعضاء في النادي الرياضي</p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <PlusIcon className="h-5 w-5" />
          إضافة اشتراك جديد
        </button>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-blue-600">{stats.total_subscriptions}</div>
            <div className="text-sm text-gray-600">إجمالي الاشتراكات</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-green-600">{stats.active_subscriptions}</div>
            <div className="text-sm text-gray-600">الاشتراكات النشطة</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-red-600">{stats.expired_subscriptions}</div>
            <div className="text-sm text-gray-600">الاشتراكات المنتهية</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-yellow-600">{stats.subscriptions_expiring_soon}</div>
            <div className="text-sm text-gray-600">تنتهي قريباً</div>
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
              <option value="expired">منتهي</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">خطة العضوية</label>
            <select
              value={filters.membership_id}
              onChange={(e) => setFilters({ ...filters, membership_id: e.target.value })}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">جميع الخطط</option>
              {memberships.map((membership) => (
                <option key={membership.id} value={membership.id}>
                  {membership.name}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Subscriptions Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">قائمة الاشتراكات</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  العضو
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  خطة العضوية
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  تاريخ البداية
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  تاريخ الانتهاء
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
              {subscriptions.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-6 py-4 text-center text-gray-500">
                    لا توجد اشتراكات
                  </td>
                </tr>
              ) : (
                subscriptions.map((subscription) => (
                  <tr key={subscription.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{subscription.user.name}</div>
                        <div className="text-sm text-gray-500">{subscription.user.email}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{subscription.membership.name}</div>
                        <div className="text-sm text-gray-500">${subscription.membership.price}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {new Date(subscription.start_date).toLocaleDateString('en-GB', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                      })}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {new Date(subscription.end_date).toLocaleDateString('en-GB', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                      })}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {getStatusBadge(subscription)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex gap-2">
                        <button
                          onClick={() => openEditModal(subscription)}
                          className="text-blue-600 hover:text-blue-900"
                        >
                          <PencilIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleRenewSubscription(subscription.id)}
                          className="text-green-600 hover:text-green-900"
                        >
                          <ArrowPathIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleCancelSubscription(subscription.id)}
                          className="text-yellow-600 hover:text-yellow-900"
                        >
                          <EyeIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleDeleteSubscription(subscription.id)}
                          className="text-red-600 hover:text-red-900"
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
                {showCreateModal ? 'إضافة اشتراك جديد' : 'تعديل الاشتراك'}
              </h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">العضو</label>
                  <select
                    value={formData.user_id}
                    onChange={(e) => setFormData({ ...formData, user_id: Number(e.target.value) })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value={0}>اختر العضو</option>
                    {/* TODO: Add users list */}
                  </select>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">خطة العضوية</label>
                  <select
                    value={formData.membership_id}
                    onChange={(e) => setFormData({ ...formData, membership_id: Number(e.target.value) })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value={0}>اختر خطة العضوية</option>
                    {memberships.map((membership) => (
                      <option key={membership.id} value={membership.id}>
                        {membership.name} - ${membership.price}
                      </option>
                    ))}
                  </select>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">تاريخ البداية</label>
                  <input
                    type="date"
                    value={formData.start_date}
                    onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">تاريخ الانتهاء</label>
                  <input
                    type="date"
                    value={formData.end_date}
                    onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    rows={3}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
              
              <div className="flex gap-3 mt-6">
                <button
                  onClick={showCreateModal ? handleCreateSubscription : handleUpdateSubscription}
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

export default Subscriptions
