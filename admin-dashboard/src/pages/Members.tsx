import React, { useState, useEffect } from 'react'
import { 
  PlusIcon, 
  MagnifyingGlassIcon, 
  FunnelIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  UserPlusIcon
} from '@heroicons/react/24/outline'
import membersApi, { Member, MembersResponse } from '../services/membersApi'

const Members: React.FC = () => {
  const [members, setMembers] = useState<Member[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [subscriptionFilter, setSubscriptionFilter] = useState<'all' | 'active' | 'inactive'>('all')
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [totalMembers, setTotalMembers] = useState(0)
  const [showAddModal, setShowAddModal] = useState(false)
  const [selectedMember, setSelectedMember] = useState<Member | null>(null)
  const [showDetailsModal, setShowDetailsModal] = useState(false)
  const [selectedMemberDetails, setSelectedMemberDetails] = useState<Member | null>(null)

  useEffect(() => {
    fetchMembers()
  }, [searchTerm, subscriptionFilter, currentPage])

  const fetchMembers = async () => {
    try {
      setLoading(true)
      setError(null)
      
      const params: any = {
        page: currentPage
      }
      
      if (searchTerm) {
        params.search = searchTerm
      }
      
      if (subscriptionFilter !== 'all') {
        params.subscription_status = subscriptionFilter
      }
      
      const response = await membersApi.getMembers(params)
      setMembers(response.data.data)
      setTotalPages(response.data.last_page)
      setTotalMembers(response.data.total)
    } catch (err: any) {
      console.error('Error fetching members:', err)
      setError(err.message || 'فشل في جلب قائمة الأعضاء')
    } finally {
      setLoading(false)
    }
  }

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    setCurrentPage(1)
    fetchMembers()
  }

  const handleFilterChange = (filter: 'all' | 'active' | 'inactive') => {
    setSubscriptionFilter(filter)
    setCurrentPage(1)
  }

  const handlePageChange = (page: number) => {
    setCurrentPage(page)
  }

  const handleDeleteMember = async (id: number) => {
    if (window.confirm('هل أنت متأكد من حذف هذا العضو؟')) {
      try {
        await membersApi.deleteMember(id)
        fetchMembers()
      } catch (err: any) {
        alert(err.message || 'فشل في حذف العضو')
      }
    }
  }

  const handleAddMember = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.currentTarget)
    
    try {
      const memberData = {
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        password: formData.get('password') as string,
        phone: formData.get('phone') as string,
        gender: formData.get('gender') as 'male' | 'female' | 'other',
        role: 'member' as const
      }
      
      await membersApi.createMember(memberData)
      setShowAddModal(false)
      fetchMembers()
      alert('تم إضافة العضو بنجاح')
    } catch (err: any) {
      alert(err.message || 'فشل في إضافة العضو')
    }
  }

  const handleEditMember = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    if (!selectedMember) return
    
    const formData = new FormData(e.currentTarget)
    
    try {
      const memberData = {
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        phone: formData.get('phone') as string,
        gender: formData.get('gender') as 'male' | 'female' | 'other'
      }
      
      await membersApi.updateMember(selectedMember.id, memberData)
      setSelectedMember(null)
      fetchMembers()
      alert('تم تحديث بيانات العضو بنجاح')
    } catch (err: any) {
      alert(err.message || 'فشل في تحديث بيانات العضو')
    }
  }

  const handleViewDetails = async (member: Member) => {
    try {
      console.log('Opening details for member:', member)
      
      // جلب معلومات مفصلة للعضو مع جميع العلاقات في استدعاء واحد
      const memberResponse = await membersApi.getMember(member.id)
      
      const memberWithDetails = {
        ...member,
        ...memberResponse.data,
        // استخدام البيانات من الاستجابة الأساسية
        subscriptions: memberResponse.data.subscriptions || [],
        workout_plans: memberResponse.data.workoutPlans || [],
        nutrition_plans: memberResponse.data.nutritionPlans || [],
        fitness_data: memberResponse.data.fitnessData || [],
        attendances: memberResponse.data.attendances || [],
        coach: memberResponse.data.coach || null
      }
      
      setSelectedMemberDetails(memberWithDetails)
      setShowDetailsModal(true)
      console.log('Modal should be visible now with full details:', memberWithDetails)
    } catch (err: any) {
      console.error('Error in handleViewDetails:', err)
      alert(err.message || 'فشل في جلب تفاصيل العضو')
    }
  }

  const getSubscriptionStatus = (member: Member) => {
    if (!member.subscriptions || member.subscriptions.length === 0) {
      return { status: 'inactive', label: 'غير مشترك', color: 'bg-red-100 text-red-800' }
    }
    
    const activeSubscription = member.subscriptions.find(sub => sub.status === 'active')
    if (activeSubscription) {
      return { status: 'active', label: 'مشترك نشط', color: 'bg-green-100 text-green-800' }
    }
    
    return { status: 'inactive', label: 'اشتراك منتهي', color: 'bg-yellow-100 text-yellow-800' }
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    })
  }

  const getGenderLabel = (gender: string) => {
    switch (gender) {
      case 'male': return 'ذكر'
      case 'female': return 'أنثى'
      case 'other': return 'آخر'
      default: return 'غير محدد'
    }
  }

  const getGenderColor = (gender: string) => {
    switch (gender) {
      case 'male': return 'bg-blue-100 text-blue-800'
      case 'female': return 'bg-pink-100 text-pink-800'
      case 'other': return 'bg-gray-100 text-gray-800'
      default: return 'bg-gray-100 text-gray-800'
    }
  }

  if (loading && members.length === 0) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">جاري تحميل قائمة الأعضاء...</p>
        </div>
      </div>
    )
  }

  if (error && members.length === 0) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="text-red-800">
          <p className="font-medium">خطأ في تحميل البيانات</p>
          <div className="text-sm mt-2">{error}</div>
          <button 
            onClick={fetchMembers}
            className="mt-4 text-sm text-red-600 hover:text-red-800 underline"
          >
            حاول مرة أخرى
          </button>
        </div>
      </div>
    )
  }

  return (
    <div>
      {/* Header */}
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">الأعضاء</h1>
          <p className="text-gray-600 mt-1">إدارة جميع أعضاء النادي الرياضي</p>
        </div>
        <button 
          onClick={() => setShowAddModal(true)}
          className="btn-primary flex items-center space-x-2"
        >
          <UserPlusIcon className="h-5 w-5" />
          <span>إضافة عضو جديد</span>
        </button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="card">
          <div className="text-2xl font-bold text-blue-600">{totalMembers}</div>
          <div className="text-sm text-gray-600">إجمالي الأعضاء</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-green-600">
            {members.filter(m => getSubscriptionStatus(m).status === 'active').length}
          </div>
          <div className="text-sm text-gray-600">المشتركين النشطين</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-orange-600">
            {members.filter(m => getSubscriptionStatus(m).status === 'inactive').length}
          </div>
          <div className="text-sm text-gray-600">غير المشتركين</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-purple-600">
            {members.filter(m => m.gender === 'male').length}
          </div>
          <div className="text-sm text-gray-600">الذكور</div>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="card mb-6">
        <div className="flex flex-col md:flex-row gap-4">
          {/* Search */}
          <form onSubmit={handleSearch} className="flex-1">
            <div className="relative">
              <MagnifyingGlassIcon className="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                placeholder="البحث عن عضو..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </form>

          {/* Filters */}
          <div className="flex items-center space-x-2">
            <FunnelIcon className="h-5 w-5 text-gray-400" />
            <select
              value={subscriptionFilter}
              onChange={(e) => handleFilterChange(e.target.value as 'all' | 'active' | 'inactive')}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">جميع الأعضاء</option>
              <option value="active">المشتركين النشطين</option>
              <option value="inactive">غير المشتركين</option>
            </select>
          </div>
        </div>
      </div>

      {/* Members Table */}
      <div className="card">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  العضو
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  البريد الإلكتروني
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الهاتف
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  حالة الاشتراك
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  تاريخ التسجيل
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {members.map((member) => {
                const subscriptionStatus = getSubscriptionStatus(member)
                return (
                  <tr key={member.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <span className="text-blue-600 font-medium text-sm">
                            {member.name.charAt(0).toUpperCase()}
                          </span>
                        </div>
                        <div className="mr-4">
                          <div className="text-sm font-medium text-gray-900">{member.name}</div>
                          <div className="text-sm text-gray-500">{member.gender || 'غير محدد'}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{member.email}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{member.phone || 'غير محدد'}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${subscriptionStatus.color}`}>
                        {subscriptionStatus.label}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {formatDate(member.created_at)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex items-center space-x-2 space-x-reverse">
                                                 <button
                           onClick={() => handleViewDetails(member)}
                           className="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                           title="عرض التفاصيل"
                         >
                           <EyeIcon className="h-4 w-4" />
                         </button>
                        <button
                          onClick={() => setSelectedMember(member)}
                          className="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                          title="تعديل"
                        >
                          <PencilIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleDeleteMember(member.id)}
                          className="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                          title="حذف"
                        >
                          <TrashIcon className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div className="flex-1 flex justify-between sm:hidden">
              <button
                onClick={() => handlePageChange(currentPage - 1)}
                disabled={currentPage === 1}
                className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                السابق
              </button>
              <button
                onClick={() => handlePageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
                className="mr-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                التالي
              </button>
            </div>
            <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p className="text-sm text-gray-700">
                  عرض <span className="font-medium">{(currentPage - 1) * 15 + 1}</span> إلى{' '}
                  <span className="font-medium">{Math.min(currentPage * 15, totalMembers)}</span> من{' '}
                  <span className="font-medium">{totalMembers}</span> نتيجة
                </p>
              </div>
              <div>
                <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                    <button
                      key={page}
                      onClick={() => handlePageChange(page)}
                      className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                        page === currentPage
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      }`}
                    >
                      {page}
                    </button>
                  ))}
                </nav>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Empty State */}
      {members.length === 0 && !loading && (
        <div className="text-center py-12">
          <UserPlusIcon className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-2 text-sm font-medium text-gray-900">لا يوجد أعضاء</h3>
          <p className="mt-1 text-sm text-gray-500">
            {searchTerm || subscriptionFilter !== 'all' 
              ? 'جرب تغيير معايير البحث' 
              : 'ابدأ بإضافة أول عضو للنادي'
            }
          </p>
          {!searchTerm && subscriptionFilter === 'all' && (
            <div className="mt-6">
              <button
                onClick={() => setShowAddModal(true)}
                className="btn-primary"
              >
                <PlusIcon className="h-5 w-5 mr-2" />
                إضافة عضو جديد
              </button>
            </div>
          )}
        </div>
      )}

      {/* Add Member Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">إضافة عضو جديد</h3>
              <form onSubmit={handleAddMember}>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الاسم</label>
                    <input
                      type="text"
                      name="name"
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                    <input
                      type="email"
                      name="email"
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">كلمة المرور</label>
                    <input
                      type="password"
                      name="password"
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الهاتف</label>
                    <input
                      type="tel"
                      name="phone"
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الجنس</label>
                    <select
                      name="gender"
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">اختر الجنس</option>
                      <option value="male">ذكر</option>
                      <option value="female">أنثى</option>
                      <option value="other">آخر</option>
                    </select>
                  </div>
                </div>
                <div className="mt-6 flex justify-end space-x-3 space-x-reverse">
                  <button
                    type="button"
                    onClick={() => setShowAddModal(false)}
                    className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300"
                  >
                    إلغاء
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                  >
                    إضافة
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Edit Member Modal */}
      {selectedMember && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">تعديل بيانات العضو</h3>
              <form onSubmit={handleEditMember}>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الاسم</label>
                    <input
                      type="text"
                      name="name"
                      defaultValue={selectedMember.name}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                    <input
                      type="email"
                      name="email"
                      defaultValue={selectedMember.email}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الهاتف</label>
                    <input
                      type="tel"
                      name="phone"
                      defaultValue={selectedMember.phone || ''}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الجنس</label>
                    <select
                      name="gender"
                      defaultValue={selectedMember.gender || ''}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">اختر الجنس</option>
                      <option value="male">ذكر</option>
                      <option value="female">أنثى</option>
                      <option value="other">آخر</option>
                    </select>
                  </div>
                </div>
                <div className="mt-6 flex justify-end space-x-3 space-x-reverse">
                  <button
                    type="button"
                    onClick={() => setSelectedMember(null)}
                    className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300"
                  >
                    إلغاء
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                  >
                    حفظ التغييرات
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Member Details Modal */}
      {showDetailsModal && selectedMemberDetails && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]">
          <div className="relative top-10 mx-auto p-6 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-2xl font-bold text-gray-900">
                  <span className="text-blue-600">👤</span> تفاصيل العضو: {selectedMemberDetails.name}
                </h3>
                <button
                  onClick={() => setShowDetailsModal(false)}
                  className="text-gray-400 hover:text-gray-600 text-2xl"
                >
                  <span className="sr-only">إغلاق</span>
                  ✕
                </button>
              </div>
              
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* معلومات العضو الأساسية */}
                <div className="lg:col-span-2">
                  <div className="bg-gray-50 rounded-lg p-6">
                    <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                      <span className="text-blue-600">📋</span> المعلومات الشخصية
                    </h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📧</span>
                        <span className="font-medium">البريد الإلكتروني:</span>
                                                 <span className="text-gray-700">{selectedMemberDetails.email || 'غير محدد'}</span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📱</span>
                        <span className="font-medium">الهاتف:</span>
                        <span className="text-gray-700">{selectedMemberDetails.phone || 'غير محدد'}</span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">👤</span>
                        <span className="font-medium">الجنس:</span>
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${getGenderColor(selectedMemberDetails.gender || '')}`}>
                          {getGenderLabel(selectedMemberDetails.gender || '')}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🎂</span>
                        <span className="font-medium">تاريخ الميلاد:</span>
                        <span className="text-gray-700">
                          {selectedMemberDetails.birth_date ? formatDate(selectedMemberDetails.birth_date) : 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📍</span>
                        <span className="font-medium">العنوان:</span>
                        <span className="text-gray-700">
                          {selectedMemberDetails.address || 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🚨</span>
                        <span className="font-medium">جهة اتصال للطوارئ:</span>
                        <span className="text-gray-700">
                          {selectedMemberDetails.emergency_contact || 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🏥</span>
                        <span className="font-medium">الحالات الطبية:</span>
                        <span className="text-gray-700">
                          {selectedMemberDetails.medical_conditions || 'لا توجد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🎯</span>
                        <span className="font-medium">أهداف اللياقة:</span>
                        <span className="text-gray-700">
                          {selectedMemberDetails.fitness_goals || 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📅</span>
                        <span className="font-medium">تاريخ التسجيل:</span>
                                                 <span className="text-gray-700">{selectedMemberDetails.created_at ? formatDate(selectedMemberDetails.created_at) : 'غير محدد'}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* الإحصائيات والمعلومات الإضافية */}
                <div>
                  <div className="bg-blue-50 rounded-lg p-6">
                    <h4 className="text-lg font-semibold text-blue-900 mb-4 border-b border-blue-200 pb-2">
                      <span className="text-blue-600">📊</span> الإحصائيات
                    </h4>
                    <div className="space-y-4">
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <span className="text-blue-500">💳</span>
                          <span className="text-sm text-gray-700">الاشتراكات</span>
                        </div>
                        <span className="text-2xl font-bold text-blue-600">{selectedMemberDetails.subscriptions?.length || 0}</span>
                      </div>
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <span className="text-green-500">👨‍🏫</span>
                          <span className="text-sm text-gray-700">المدرب</span>
                        </div>
                        <span className="text-2xl font-bold text-green-600">
                          {selectedMemberDetails.coach ? 'نعم' : 'لا'}
                        </span>
                      </div>
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <span className="text-purple-500">📈</span>
                          <span className="text-sm text-gray-700">مستوى الخبرة</span>
                        </div>
                        <span className="text-2xl font-bold text-purple-600">
                          {selectedMemberDetails.experience_level ? 
                            (selectedMemberDetails.experience_level === 'beginner' ? 'مبتدئ' :
                             selectedMemberDetails.experience_level === 'intermediate' ? 'متوسط' : 'متقدم') 
                            : 'غير محدد'}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* قائمة الاشتراكات */}
              {selectedMemberDetails.subscriptions && selectedMemberDetails.subscriptions.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-green-600">💳</span> الاشتراكات
                  </h4>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {/* عرض الاشتراك النشط فقط */}
                      {selectedMemberDetails.subscriptions
                        .filter(subscription => subscription.is_active)
                        .map((subscription) => (
                        <div key={subscription.id} className="bg-white p-4 rounded-lg shadow-sm border">
                          <div className="flex items-center justify-between mb-2">
                            <h5 className="font-medium text-gray-900">{subscription.membership.name}</h5>
                            <span className="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                              نشط
                            </span>
                          </div>
                          <div className="text-sm text-gray-600 space-y-1">
                            <div>من: {formatDate(subscription.start_date)}</div>
                            <div>إلى: {formatDate(subscription.end_date)}</div>
                            <div>السعر: {subscription.membership.price} ريال</div>
                            <div className="text-xs text-gray-500">
                              {subscription.notes && `ملاحظات: ${subscription.notes}`}
                            </div>
                          </div>
                        </div>
                      ))}
                      
                      {/* عرض رسالة إذا لم يكن هناك اشتراك نشط */}
                      {selectedMemberDetails.subscriptions.filter(subscription => subscription.is_active).length === 0 && (
                        <div className="col-span-full text-center py-8 text-gray-500">
                          <div className="text-4xl mb-2">💳</div>
                          <p>لا يوجد اشتراك نشط حالياً</p>
                        </div>
                      )}
                    </div>
                    
                    {/* عرض جميع الاشتراكات (غير النشطة) في قسم منفصل */}
                    {selectedMemberDetails.subscriptions.filter(subscription => !subscription.is_active).length > 0 && (
                      <div className="mt-4">
                        <h6 className="text-sm font-medium text-gray-700 mb-2">الاشتراكات السابقة:</h6>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                          {selectedMemberDetails.subscriptions
                            .filter(subscription => !subscription.is_active)
                            .map((subscription) => (
                            <div key={subscription.id} className="bg-gray-100 p-3 rounded-lg border border-gray-200">
                              <div className="flex items-center justify-between mb-2">
                                <h6 className="font-medium text-gray-700 text-sm">{subscription.membership.name}</h6>
                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                  subscription.status === 'expired' ? 'bg-red-100 text-red-800' :
                                  'bg-yellow-100 text-yellow-800'
                                }`}>
                                  {subscription.status === 'expired' ? 'منتهي' : 'ملغي'}
                                </span>
                              </div>
                              <div className="text-xs text-gray-600 space-y-1">
                                <div>من: {formatDate(subscription.start_date)}</div>
                                <div>إلى: {formatDate(subscription.end_date)}</div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* معلومات المدرب */}
              {selectedMemberDetails.coach && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-green-600">👨‍🏫</span> المدرب المسند
                  </h4>
                  <div className="bg-green-50 rounded-lg p-4">
                    <div className="flex items-center space-x-2 space-x-reverse">
                                             <div className="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                         <span className="text-green-600 font-medium text-lg">
                           {selectedMemberDetails.coach?.name?.charAt(0)?.toUpperCase() || '?'}
                         </span>
                       </div>
                       <div>
                         <h5 className="font-medium text-gray-900">{selectedMemberDetails.coach?.name || 'غير محدد'}</h5>
                         <p className="text-sm text-gray-600">{selectedMemberDetails.coach?.email || 'غير محدد'}</p>
                         {selectedMemberDetails.coach?.experience_level && (
                           <p className="text-xs text-gray-500">
                             مستوى الخبرة: {selectedMemberDetails.coach.experience_level}
                           </p>
                         )}
                       </div>
                    </div>
                  </div>
                </div>
              )}

              {/* خطط التمارين */}
              {selectedMemberDetails.workout_plans && selectedMemberDetails.workout_plans.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-orange-600">💪</span> خطط التمارين
                  </h4>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {selectedMemberDetails.workout_plans.map((plan) => (
                        <div key={plan.id} className="bg-white p-4 rounded-lg shadow-sm border">
                          <h5 className="font-medium text-gray-900 mb-2">{plan.title}</h5>
                          {plan.description && <p className="text-sm text-gray-600 mb-2">{plan.description}</p>}
                          <div className="text-sm text-gray-600 space-y-1">
                            {plan.start_date && <div>من: {formatDate(plan.start_date)}</div>}
                            {plan.end_date && <div>إلى: {formatDate(plan.end_date)}</div>}
                            <div>المدرب: {plan.coach?.name || 'غير محدد'}</div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* خطط التغذية */}
              {selectedMemberDetails.nutrition_plans && selectedMemberDetails.nutrition_plans.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-green-600">🥗</span> خطط التغذية
                  </h4>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {selectedMemberDetails.nutrition_plans.map((plan) => (
                        <div key={plan.id} className="bg-white p-4 rounded-lg shadow-sm border">
                          <h5 className="font-medium text-gray-900 mb-2">{plan.title}</h5>
                          {plan.description && <p className="text-sm text-gray-600 mb-2">{plan.description}</p>}
                          <div className="text-sm text-gray-600 space-y-1">
                            {plan.start_date && <div>من: {formatDate(plan.start_date)}</div>}
                            {plan.end_date && <div>إلى: {formatDate(plan.end_date)}</div>}
                            <div>المدرب: {plan.coach?.name || 'غير محدد'}</div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* بيانات اللياقة */}
              {selectedMemberDetails.fitness_data && selectedMemberDetails.fitness_data.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-purple-600">📊</span> بيانات اللياقة
                  </h4>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {selectedMemberDetails.fitness_data.map((data, index) => (
                        <div key={index} className="bg-white p-4 rounded-lg shadow-sm border">
                          <div className="text-sm text-gray-600 space-y-1">
                            <div>الوزن: {data.weight} كجم</div>
                            <div>الطول: {data.height} سم</div>
                            {data.bmi && <div>مؤشر كتلة الجسم: {data.bmi}</div>}
                            {data.fat_percent && <div>نسبة الدهون: {data.fat_percent}%</div>}
                            {data.muscle_mass && <div>نسبة العضل: {data.muscle_mass}%</div>}
                            <div>التاريخ: {formatDate(data.created_at)}</div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* سجلات الحضور */}
              {selectedMemberDetails.attendances && selectedMemberDetails.attendances.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <span className="text-blue-600">📅</span> سجلات الحضور
                  </h4>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {selectedMemberDetails.attendances.slice(0, 10).map((attendance, index) => (
                        <div key={index} className="bg-white p-4 rounded-lg shadow-sm border">
                          <div className="text-sm text-gray-600 space-y-1">
                            <div>دخول: {new Date(attendance.check_in_time).toLocaleString('en-GB', {
                              year: 'numeric',
                              month: '2-digit',
                              day: '2-digit',
                              hour: '2-digit',
                              minute: '2-digit'
                            })}</div>
                            {attendance.check_out_time && (
                              <div>خروج: {new Date(attendance.check_out_time).toLocaleString('en-GB', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                              })}</div>
                            )}
                            <div>التاريخ: {formatDate(attendance.created_at)}</div>
                          </div>
                        </div>
                      ))}
                    </div>
                    {selectedMemberDetails.attendances.length > 10 && (
                      <div className="mt-4 text-center text-sm text-gray-600">
                        عرض آخر 10 سجلات من أصل {selectedMemberDetails.attendances.length}
                      </div>
                    )}
                  </div>
                </div>
              )}

              <div className="mt-6 flex justify-end">
                <button
                  onClick={() => setShowDetailsModal(false)}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300"
                >
                  إغلاق
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Members
