import React, { useState, useEffect } from 'react';
import fitnessDataApi, { FitnessData, FitnessDataFormData, FitnessDataStats } from '../services/fitnessDataApi';
import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon, 
  ChartBarIcon,
  UserIcon,
  ScaleIcon,
  Square3Stack3DIcon
} from '@heroicons/react/24/outline';

const FitnessDataPage: React.FC = () => {
  const [fitnessData, setFitnessData] = useState<FitnessData[]>([]);
  const [stats, setStats] = useState<FitnessDataStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [editingData, setEditingData] = useState<FitnessData | null>(null);
  const [formData, setFormData] = useState<FitnessDataFormData>({
    user_id: 0,
    weight: 0,
    height: 0,
    bmi: 0,
    fat_percent: 0,
    muscle_mass: 0,
    body_fat_percentage: 0,
    waist_circumference: 0,
    chest_circumference: 0,
    arm_circumference: 0,
    leg_circumference: 0,
    notes: ''
  });
  const [filters, setFilters] = useState({
    user_id: '',
    start_date: '',
    end_date: '',
    period: 'month' as 'week' | 'month' | 'year'
  });

  useEffect(() => {
    fetchData();
    fetchStats();
  }, [filters.period]);

  const fetchData = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await fitnessDataApi.getFitnessData({
        user_id: filters.user_id ? parseInt(filters.user_id) : undefined,
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined,
        period: filters.period
      });
      setFitnessData(data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      const statsData = await fitnessDataApi.getFitnessDataStats(filters.period);
      setStats(statsData);
    } catch (err: any) {
      console.error('Error fetching stats:', err);
    }
  };

  const handleCreate = async () => {
    try {
      if (formData.weight > 0 && formData.height > 0) {
        formData.bmi = fitnessDataApi.calculateBMI(formData.weight, formData.height);
      }
      
      await fitnessDataApi.createFitnessData(formData);
      setShowCreateModal(false);
      resetForm();
      fetchData();
      fetchStats();
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleUpdate = async () => {
    if (!editingData) return;
    
    try {
      if (formData.weight > 0 && formData.height > 0) {
        formData.bmi = fitnessDataApi.calculateBMI(formData.weight, formData.height);
      }
      
      await fitnessDataApi.updateFitnessData(editingData.id, formData);
      setShowEditModal(false);
      setEditingData(null);
      resetForm();
      fetchData();
      fetchStats();
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('هل أنت متأكد من حذف هذه البيانات؟')) return;
    
    try {
      await fitnessDataApi.deleteFitnessData(id);
      fetchData();
      fetchStats();
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleEdit = (data: FitnessData) => {
    setEditingData(data);
    setFormData({
      user_id: data.user_id,
      weight: data.weight,
      height: data.height,
      bmi: data.bmi || 0,
      fat_percent: data.fat_percent || 0,
      muscle_mass: data.muscle_mass || 0,
      body_fat_percentage: data.body_fat_percentage || 0,
      waist_circumference: data.waist_circumference || 0,
      chest_circumference: data.chest_circumference || 0,
      arm_circumference: data.arm_circumference || 0,
      leg_circumference: data.leg_circumference || 0,
      notes: data.notes || ''
    });
    setShowEditModal(true);
  };

  const resetForm = () => {
    setFormData({
      user_id: 0,
      weight: 0,
      height: 0,
      bmi: 0,
      fat_percent: 0,
      muscle_mass: 0,
      body_fat_percentage: 0,
      waist_circumference: 0,
      chest_circumference: 0,
      arm_circumference: 0,
      leg_circumference: 0,
      notes: ''
    });
  };

  const getBMICategory = (bmi: number) => {
    return fitnessDataApi.getBMICategory(bmi);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">جاري تحميل بيانات اللياقة البدنية...</p>
        </div>
      </div>
    );
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">بيانات اللياقة البدنية</h1>
        <button
          onClick={() => setShowCreateModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <PlusIcon className="h-5 w-5" />
          إضافة بيانات جديدة
        </button>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <div className="card">
            <div className="text-2xl font-bold text-blue-600">{stats.total_records.toLocaleString()}</div>
            <div className="text-sm text-gray-600">إجمالي السجلات</div>
          </div>
          
          <div className="card">
            <div className="text-2xl font-bold text-green-600">
              {stats.average_weight ? stats.average_weight.toFixed(1) : '0'} kg
            </div>
            <div className="text-sm text-gray-600">متوسط الوزن</div>
          </div>
          
          <div className="card">
            <div className="text-2xl font-bold text-purple-600">
              {stats.average_bmi ? stats.average_bmi.toFixed(1) : '0'}
            </div>
            <div className="text-sm text-gray-600">متوسط مؤشر كتلة الجسم</div>
          </div>
          
          <div className="card">
            <div className="text-2xl font-bold text-orange-600">
              {stats.average_body_fat ? stats.average_body_fat.toFixed(1) : '0'}%
            </div>
            <div className="text-sm text-gray-600">متوسط نسبة الدهون</div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="bg-white p-4 rounded-lg shadow mb-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">العضو</label>
            <input
              type="text"
              placeholder="معرف العضو"
              value={filters.user_id}
              onChange={(e) => setFilters({ ...filters, user_id: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
            <input
              type="date"
              value={filters.start_date}
              onChange={(e) => setFilters({ ...filters, start_date: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
            <input
              type="date"
              value={filters.end_date}
              onChange={(e) => setFilters({ ...filters, end_date: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">الفترة</label>
            <select
              value={filters.period}
              onChange={(e) => setFilters({ ...filters, period: e.target.value as 'week' | 'month' | 'year' })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="week">أسبوع</option>
              <option value="month">شهر</option>
              <option value="year">سنة</option>
            </select>
          </div>
        </div>
        
        <div className="mt-4 flex gap-2">
          <button
            onClick={fetchData}
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
          >
            تطبيق الفلاتر
          </button>
          <button
            onClick={() => {
              setFilters({ user_id: '', start_date: '', end_date: '', period: 'month' });
              fetchData();
            }}
            className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700"
          >
            إعادة تعيين
          </button>
        </div>
      </div>

      {/* Error Display */}
      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div className="text-red-800">{error}</div>
        </div>
      )}

      {/* Fitness Data Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  العضو
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الوزن (kg)
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الطول (cm)
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  مؤشر كتلة الجسم
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  نسبة الدهون
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  كتلة العضل
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  التاريخ
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {fitnessData.map((data) => {
                const bmiCategory = data.bmi ? getBMICategory(data.bmi) : null;
                return (
                  <tr key={data.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <UserIcon className="h-5 w-5 text-gray-400 mr-2" />
                        <div>
                          <div className="text-sm font-medium text-gray-900">
                            {data.user_name || `العضو ${data.user_id}`}
                          </div>
                          <div className="text-sm text-gray-500">
                            {data.user_email || 'غير متوفر'}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <ScaleIcon className="h-5 w-5 text-gray-400 mr-2" />
                        <span className="text-sm text-gray-900">{data.weight} kg</span>
                      </div>
                    </td>
                                         <td className="px-6 py-4 whitespace-nowrap">
                       <div className="flex items-center">
                         <Square3Stack3DIcon className="h-5 w-5 text-gray-400 mr-2" />
                         <span className="text-sm text-gray-900">{data.height} cm</span>
                       </div>
                     </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {data.bmi ? (
                        <div>
                          <span className={`text-sm font-medium ${bmiCategory?.color}`}>
                            {data.bmi}
                          </span>
                          <div className="text-xs text-gray-500">{bmiCategory?.category}</div>
                        </div>
                      ) : (
                        <span className="text-sm text-gray-400">غير محسوب</span>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {data.fat_percent || data.body_fat_percentage ? 
                        `${data.fat_percent || data.body_fat_percentage}%` : 
                        'غير متوفر'
                      }
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {data.muscle_mass ? `${data.muscle_mass}%` : 'غير متوفر'}
                    </td>
                                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                       {new Date(data.created_at).toLocaleDateString('en-GB', {
                         year: 'numeric',
                         month: '2-digit',
                         day: '2-digit'
                       })}
                     </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex gap-2">
                        <button
                          onClick={() => handleEdit(data)}
                          className="text-blue-600 hover:text-blue-900"
                        >
                          <PencilIcon className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => handleDelete(data.id)}
                          className="text-red-600 hover:text-red-900"
                        >
                          <TrashIcon className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
        
        {fitnessData.length === 0 && (
          <div className="text-center py-12">
            <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">لا توجد بيانات</h3>
            <p className="mt-1 text-sm text-gray-500">ابدأ بإضافة بيانات لياقة بدنية جديدة.</p>
          </div>
        )}
      </div>

      {/* Create Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">إضافة بيانات لياقة بدنية جديدة</h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">معرف العضو</label>
                  <input
                    type="number"
                    value={formData.user_id}
                    onChange={(e) => setFormData({ ...formData, user_id: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="أدخل معرف العضو"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">الوزن (kg)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.weight}
                    onChange={(e) => setFormData({ ...formData, weight: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="أدخل الوزن"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">الطول (cm)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.height}
                    onChange={(e) => setFormData({ ...formData, height: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="أدخل الطول"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">نسبة الدهون (%)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.fat_percent}
                    onChange={(e) => setFormData({ ...formData, fat_percent: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="أدخل نسبة الدهون"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">كتلة العضل (%)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.muscle_mass}
                    onChange={(e) => setFormData({ ...formData, muscle_mass: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="أدخل كتلة العضل"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    rows={3}
                    placeholder="أدخل ملاحظات إضافية"
                  />
                </div>
              </div>
              
              <div className="flex gap-2 mt-6">
                <button
                  onClick={handleCreate}
                  className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                >
                  إضافة
                </button>
                <button
                  onClick={() => {
                    setShowCreateModal(false);
                    resetForm();
                  }}
                  className="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700"
                >
                  إلغاء
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Edit Modal */}
      {showEditModal && editingData && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">تعديل بيانات اللياقة البدنية</h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">الوزن (kg)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.weight}
                    onChange={(e) => setFormData({ ...formData, weight: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">الطول (cm)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.height}
                    onChange={(e) => setFormData({ ...formData, height: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">نسبة الدهون (%)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.fat_percent}
                    onChange={(e) => setFormData({ ...formData, fat_percent: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">كتلة العضل (%)</label>
                  <input
                    type="number"
                    step="0.1"
                    value={formData.muscle_mass}
                    onChange={(e) => setFormData({ ...formData, muscle_mass: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    rows={3}
                  />
                </div>
              </div>
              
              <div className="flex gap-2 mt-6">
                <button
                  onClick={handleUpdate}
                  className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                >
                  تحديث
                </button>
                <button
                  onClick={() => {
                    setShowEditModal(false);
                    setEditingData(null);
                    resetForm();
                  }}
                  className="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700"
                >
                  إلغاء
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default FitnessDataPage;
