import React, { useState, useEffect } from 'react'
import { 
  PlusIcon, 
  MagnifyingGlassIcon, 
  FunnelIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  UserPlusIcon,
  UsersIcon,
  ClockIcon,
  AcademicCapIcon
} from '@heroicons/react/24/outline'
import coachesApi, { Coach, CoachesResponse } from '../services/coachesApi'

const Coaches: React.FC = () => {
  const [coaches, setCoaches] = useState<Coach[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [genderFilter, setGenderFilter] = useState<'all' | 'male' | 'female'>('all')
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [totalCoaches, setTotalCoaches] = useState(0)
  const [showAddModal, setShowAddModal] = useState(false)
  const [selectedCoach, setSelectedCoach] = useState<Coach | null>(null)
  const [showDetailsModal, setShowDetailsModal] = useState(false)
  const [selectedCoachDetails, setSelectedCoachDetails] = useState<Coach | null>(null)

  useEffect(() => {
    fetchCoaches()
  }, [searchTerm, genderFilter, currentPage])

  const fetchCoaches = async () => {
    try {
      setLoading(true)
      setError(null)
      
      const params: any = {
        page: currentPage
      }
      
      if (searchTerm) {
        params.search = searchTerm
      }
      
      if (genderFilter !== 'all') {
        params.gender = genderFilter
      }
      
      const response = await coachesApi.getCoaches(params)
      setCoaches(response.data.data)
      setTotalPages(response.data.last_page)
      setTotalCoaches(response.data.total)
    } catch (err: any) {
      console.error('Error fetching coaches:', err)
      setError(err.message || 'فشل في جلب قائمة المدربين')
    } finally {
      setLoading(false)
    }
  }

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    setCurrentPage(1)
    fetchCoaches()
  }

  const handleFilterChange = (filter: 'all' | 'male' | 'female') => {
    setGenderFilter(filter)
    setCurrentPage(1)
  }

  const handlePageChange = (page: number) => {
    setCurrentPage(page)
  }

  const handleDeleteCoach = async (id: number) => {
    if (window.confirm('هل أنت متأكد من حذف هذا المدرب؟')) {
      try {
        await coachesApi.deleteCoach(id)
        fetchCoaches()
        alert('تم حذف المدرب بنجاح')
      } catch (err: any) {
        alert(err.message || 'فشل في حذف المدرب')
      }
    }
  }

  const handleAddCoach = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.currentTarget)
    
    try {
      const coachData = {
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        password: formData.get('password') as string,
        phone: formData.get('phone') as string,
        gender: formData.get('gender') as 'male' | 'female',
        birth_date: formData.get('birth_date') as string || undefined,
        role: 'coach' as const
      }
      
      await coachesApi.createCoach(coachData)
      setShowAddModal(false)
      fetchCoaches()
      alert('تم إضافة المدرب بنجاح')
    } catch (err: any) {
      alert(err.message || 'فشل في إضافة المدرب')
    }
  }

  const handleEditCoach = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    if (!selectedCoach) return
    
    const formData = new FormData(e.currentTarget)
    
    try {
      const coachData = {
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        phone: formData.get('phone') as string,
        gender: formData.get('gender') as 'male' | 'female',
        birth_date: formData.get('birth_date') as string || undefined
      }
      
      await coachesApi.updateCoach(selectedCoach.id, coachData)
      setSelectedCoach(null)
      fetchCoaches()
      alert('تم تحديث بيانات المدرب بنجاح')
    } catch (err: any) {
      alert(err.message || 'فشل في تحديث بيانات المدرب')
    }
  }

  const handleViewDetails = async (coach: Coach) => {
    try {
      const [membersResponse, workoutPlansResponse, nutritionPlansResponse] = await Promise.all([
        coachesApi.getCoachMembers(coach.id),
        coachesApi.getCoachWorkoutPlans(coach.id),
        coachesApi.getCoachNutritionPlans(coach.id)
      ])

      const coachWithDetails = {
        ...coach,
        members: membersResponse.data,
        workoutPlans: workoutPlansResponse.data,
        nutritionPlans: nutritionPlansResponse.data
      }

      setSelectedCoachDetails(coachWithDetails)
      setShowDetailsModal(true)
    } catch (err: any) {
      alert(err.message || 'فشل في جلب تفاصيل المدرب')
    }
  }

  const getGenderLabel = (gender: string) => {
    switch (gender) {
      case 'male': return 'ذكر'
      case 'female': return 'أنثى'
      default: return 'غير محدد'
    }
  }

  const getGenderColor = (gender: string) => {
    switch (gender) {
      case 'male': return 'bg-blue-100 text-blue-800'
      case 'female': return 'bg-pink-100 text-pink-800'
      default: return 'bg-gray-100 text-gray-800'
    }
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    })
  }

  if (loading && coaches.length === 0) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">جاري تحميل قائمة المدربين...</p>
        </div>
      </div>
    )
  }

  if (error && coaches.length === 0) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="text-red-800">
          <p className="font-medium">خطأ في تحميل البيانات</p>
          <div className="text-sm mt-2">{error}</div>
          <button 
            onClick={fetchCoaches}
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
          <h1 className="text-3xl font-bold text-gray-900">المدربين</h1>
          <p className="text-gray-600 mt-1">إدارة جميع مدربي النادي الرياضي</p>
        </div>
        <button 
          onClick={() => setShowAddModal(true)}
          className="btn-primary flex items-center space-x-2"
        >
          <UserPlusIcon className="h-5 w-5" />
          <span>إضافة مدرب جديد</span>
        </button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="card">
          <div className="text-2xl font-bold text-blue-600">{totalCoaches}</div>
          <div className="text-sm text-gray-600">إجمالي المدربين</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-green-600">
            {coaches.filter(c => c.gender === 'male').length}
          </div>
          <div className="text-sm text-gray-600">الذكور</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-pink-600">
            {coaches.filter(c => c.gender === 'female').length}
          </div>
          <div className="text-sm text-gray-600">الإناث</div>
        </div>
        <div className="card">
          <div className="text-2xl font-bold text-purple-600">
            {coaches.filter(c => c.birth_date).length}
          </div>
          <div className="text-sm text-gray-600">مع تاريخ الميلاد</div>
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
                placeholder="البحث عن مدرب..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </form>

          {/* Gender Filter */}
          <div className="flex items-center space-x-2">
            <FunnelIcon className="h-5 w-5 text-gray-400" />
            <select
              value={genderFilter}
              onChange={(e) => handleFilterChange(e.target.value as 'all' | 'male' | 'female')}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">جميع الأجناس</option>
              <option value="male">ذكر</option>
              <option value="female">أنثى</option>
            </select>
          </div>
        </div>
      </div>

      {/* Coaches Table */}
      <div className="card">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  المدرب
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  البريد الإلكتروني
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الجنس
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  تاريخ الميلاد
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
              {coaches.map((coach) => (
                <tr key={coach.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div 
                        className="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center cursor-pointer hover:bg-green-200 transition-colors"
                        onClick={() => handleViewDetails(coach)}
                        title="انقر لعرض التفاصيل"
                      >
                        <span className="text-green-600 font-medium text-sm">
                          {coach.name.charAt(0).toUpperCase()}
                        </span>
                      </div>
                      <div className="mr-4">
                        <div 
                          className="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                          onClick={() => handleViewDetails(coach)}
                          title="انقر لعرض التفاصيل"
                        >
                          {coach.name}
                        </div>
                        <div className="text-sm text-gray-500">{coach.phone}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{coach.email}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getGenderColor(coach.gender)}`}>
                      {getGenderLabel(coach.gender)}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {coach.birth_date ? formatDate(coach.birth_date) : 'غير محدد'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {formatDate(coach.created_at)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2 space-x-reverse">
                      <button
                        onClick={() => handleViewDetails(coach)}
                        className="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                        title="عرض التفاصيل"
                      >
                        <EyeIcon className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => setSelectedCoach(coach)}
                        className="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                        title="تعديل"
                      >
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => handleDeleteCoach(coach.id)}
                        className="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                        title="حذف"
                      >
                        <TrashIcon className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
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
                  <span className="font-medium">{Math.min(currentPage * 15, totalCoaches)}</span> من{' '}
                  <span className="font-medium">{totalCoaches}</span> نتيجة
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
      {coaches.length === 0 && !loading && (
        <div className="text-center py-12">
          <UserPlusIcon className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-2 text-sm font-medium text-gray-900">لا يوجد مدربين</h3>
          <p className="mt-1 text-sm text-gray-500">
            {searchTerm || genderFilter !== 'all'
              ? 'جرب تغيير معايير البحث' 
              : 'ابدأ بإضافة أول مدرب للنادي'
            }
          </p>
          {!searchTerm && genderFilter === 'all' && (
            <div className="mt-6">
              <button
                onClick={() => setShowAddModal(true)}
                className="btn-primary"
              >
                <PlusIcon className="h-5 w-5 mr-2" />
                إضافة مدرب جديد
              </button>
            </div>
          )}
        </div>
      )}

      {/* Add Coach Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">إضافة مدرب جديد</h3>
              <form onSubmit={handleAddCoach}>
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
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الجنس</label>
                    <select
                      name="gender"
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">اختر الجنس</option>
                      <option value="male">ذكر</option>
                      <option value="female">أنثى</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">تاريخ الميلاد</label>
                    <input
                      type="date"
                      name="birth_date"
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
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

      {/* Edit Coach Modal */}
      {selectedCoach && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">تعديل بيانات المدرب</h3>
              <form onSubmit={handleEditCoach}>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الاسم</label>
                    <input
                      type="text"
                      name="name"
                      defaultValue={selectedCoach.name}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                    <input
                      type="email"
                      name="email"
                      defaultValue={selectedCoach.email}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الهاتف</label>
                    <input
                      type="tel"
                      name="phone"
                      defaultValue={selectedCoach.phone}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">الجنس</label>
                    <select
                      name="gender"
                      defaultValue={selectedCoach.gender}
                      required
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">اختر الجنس</option>
                      <option value="male">ذكر</option>
                      <option value="female">أنثى</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">تاريخ الميلاد</label>
                    <input
                      type="date"
                      name="birth_date"
                      defaultValue={selectedCoach.birth_date || ''}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                <div className="mt-6 flex justify-end space-x-3 space-x-reverse">
                  <button
                    type="button"
                    onClick={() => setSelectedCoach(null)}
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

      {/* Coach Details Modal */}
      {showDetailsModal && selectedCoachDetails && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-4xl shadow-lg rounded-md bg-white max-w-4xl">
            <div className="mt-3">
              <div className="flex justify-between items-center mb-6">
                <div className="flex items-center space-x-4 space-x-reverse">
                  <div className="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                    <span className="text-green-600 font-bold text-xl">
                      {selectedCoachDetails.name.charAt(0).toUpperCase()}
                    </span>
                  </div>
                  <div>
                    <h3 className="text-2xl font-bold text-gray-900">{selectedCoachDetails.name}</h3>
                    <p className="text-gray-600">مدرب في النادي الرياضي</p>
                  </div>
                </div>
                <button
                  onClick={() => setShowDetailsModal(false)}
                  className="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100"
                >
                  <span className="sr-only">إغلاق</span>
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* معلومات المدرب الأساسية */}
                <div className="lg:col-span-2">
                  <div className="bg-gray-50 rounded-lg p-6">
                    <h4 className="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                      <span className="text-blue-600">📋</span> المعلومات الشخصية
                    </h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📧</span>
                        <span className="font-medium">البريد الإلكتروني:</span>
                        <span className="text-gray-700">{selectedCoachDetails.email}</span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📱</span>
                        <span className="font-medium">الهاتف:</span>
                        <span className="text-gray-700">{selectedCoachDetails.phone}</span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">👤</span>
                        <span className="font-medium">الجنس:</span>
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${getGenderColor(selectedCoachDetails.gender)}`}>
                          {getGenderLabel(selectedCoachDetails.gender)}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🎂</span>
                        <span className="font-medium">تاريخ الميلاد:</span>
                        <span className="text-gray-700">
                          {selectedCoachDetails.birth_date ? formatDate(selectedCoachDetails.birth_date) : 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">🆔</span>
                        <span className="font-medium">معرف البصمة:</span>
                        <span className="text-gray-700">
                          {selectedCoachDetails.fingerprint_id || 'غير محدد'}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 space-x-reverse">
                        <span className="text-gray-500">📅</span>
                        <span className="font-medium">تاريخ التسجيل:</span>
                        <span className="text-gray-700">{formatDate(selectedCoachDetails.created_at)}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* الإحصائيات */}
                <div>
                  <div className="bg-blue-50 rounded-lg p-6">
                    <h4 className="text-lg font-semibold text-blue-900 mb-4 border-b border-blue-200 pb-2">
                      <span className="text-blue-600">📊</span> الإحصائيات
                    </h4>
                    <div className="space-y-4">
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <UsersIcon className="h-5 w-5 text-blue-500" />
                          <span className="text-sm text-gray-700">الأعضاء</span>
                        </div>
                        <span className="text-2xl font-bold text-blue-600">{selectedCoachDetails.members?.length || 0}</span>
                      </div>
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <ClockIcon className="h-5 w-5 text-green-500" />
                          <span className="text-sm text-gray-700">خطط التمارين</span>
                        </div>
                        <span className="text-2xl font-bold text-green-600">{selectedCoachDetails.workoutPlans?.length || 0}</span>
                      </div>
                      <div className="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <AcademicCapIcon className="h-5 w-5 text-purple-500" />
                          <span className="text-sm text-gray-700">خطط التغذية</span>
                        </div>
                        <span className="text-2xl font-bold text-purple-600">{selectedCoachDetails.nutritionPlans?.length || 0}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* قائمة الأعضاء */}
              {selectedCoachDetails.members && selectedCoachDetails.members.length > 0 && (
                <div className="mt-6">
                  <h4 className="text-md font-medium text-gray-900 mb-3">الأعضاء المسندين</h4>
                  <div className="bg-gray-50 rounded-lg p-3">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                      {selectedCoachDetails.members.map((member) => (
                        <div key={member.id} className="flex items-center space-x-2 space-x-reverse">
                          <div className="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
                            <span className="text-blue-600 text-xs font-medium">
                              {member.name.charAt(0).toUpperCase()}
                            </span>
                          </div>
                          <span className="text-sm text-gray-700">{member.name}</span>
                          <span className="text-xs text-gray-500">({member.email})</span>
                        </div>
                      ))}
                    </div>
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

export default Coaches
