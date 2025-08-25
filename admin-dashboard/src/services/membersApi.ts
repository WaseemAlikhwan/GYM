import api from './api'

export interface Member {
  id: number
  name: string
  email: string
  phone?: string
  role: 'member' | 'coach' | 'admin'
  gender?: 'male' | 'female' | 'other'
  birth_date?: string
  address?: string
  emergency_contact?: string
  medical_conditions?: string
  fitness_goals?: string
  experience_level?: 'beginner' | 'intermediate' | 'advanced'
  specializations?: string[]
  status?: 'active' | 'inactive' | 'suspended'
  created_at: string
  updated_at: string
  subscriptions?: Subscription[]
  coach?: Coach | null
  workout_plans?: WorkoutPlan[]
  nutrition_plans?: NutritionPlan[]
  fitness_data?: FitnessData[]
  attendances?: Attendance[]
}

export interface Subscription {
  id: number
  user_id: number
  membership_id: number
  start_date: string
  end_date: string
  is_active: boolean
  status: 'active' | 'expired' | 'cancelled' | 'suspended'
  notes?: string
  membership: Membership
}

export interface Membership {
  id: number
  name: string
  description?: string
  price: number
  duration_days: number
  has_coach: boolean
  has_workout_plan: boolean
  has_nutrition_plan: boolean
  is_active: boolean
}

export interface Coach {
  id: number
  name: string
  email: string
  experience_level?: string
  specializations?: string[]
}

export interface WorkoutPlan {
  id: number
  user_id: number
  coach_id: number
  title: string
  description?: string
  start_date?: string
  end_date?: string
  created_at: string
  updated_at: string
  coach?: Coach
}

export interface NutritionPlan {
  id: number
  user_id: number
  coach_id: number
  title: string
  description?: string
  start_date?: string
  end_date?: string
  created_at: string
  updated_at: string
  coach?: Coach
}

export interface FitnessData {
  id: number
  user_id: number
  weight: number
  height: number
  bmi?: number
  fat_percent?: number
  muscle_mass?: number
  created_at: string
  updated_at: string
}

export interface Attendance {
  id: number
  user_id: number
  check_in_time: string
  check_out_time?: string
  created_at: string
  updated_at: string
}

export interface MembersResponse {
  success: boolean
  data: {
    data: Member[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface MemberStats {
  total_users: number
  total_admins: number
  total_coaches: number
  total_members: number
  active_users: number
  inactive_users: number
  suspended_users: number
  new_users_this_month: number
  users_by_gender: {
    male: number
    female: number
    other: number
  }
  coaches_by_experience: {
    beginner: number
    intermediate: number
    advanced: number
  }
}

class MembersApi {
  // الحصول على قائمة الأعضاء
  async getMembers(params?: {
    search?: string
    subscription_status?: 'active' | 'inactive'
    page?: number
  }): Promise<MembersResponse> {
    try {
      const response = await api.get('/users/members/list', { params })
      return response.data
    } catch (error: any) {
      console.error('Error fetching members:', error)
      throw new Error(error.response?.data?.message || 'فشل في جلب قائمة الأعضاء')
    }
  }

  // الحصول على عضو محدد
  async getMember(id: number): Promise<{ success: boolean; data: Member }> {
    try {
      const response = await api.get(`/users/${id}`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member:', error)
      throw new Error(error.response?.data?.message || 'فشل في جلب بيانات العضو')
    }
  }

  // إنشاء عضو جديد
  async createMember(memberData: Partial<Member>): Promise<{ success: boolean; message: string; data: Member }> {
    try {
      const response = await api.post('/users', memberData)
      return response.data
    } catch (error: any) {
      console.error('Error creating member:', error)
      if (error.response?.data?.errors) {
        const errorMessages = Object.values(error.response.data.errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      throw new Error(error.response?.data?.message || 'فشل في إنشاء العضو')
    }
  }

  // تحديث بيانات العضو
  async updateMember(id: number, memberData: Partial<Member>): Promise<{ success: boolean; message: string; data: Member }> {
    try {
      const response = await api.put(`/users/${id}`, memberData)
      return response.data
    } catch (error: any) {
      console.error('Error updating member:', error)
      if (error.response?.data?.errors) {
        const errorMessages = Object.values(error.response.data.errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      throw new Error(error.response?.data?.message || 'فشل في تحديث بيانات العضو')
    }
  }

  // حذف العضو
  async deleteMember(id: number): Promise<{ success: boolean; message: string }> {
    try {
      const response = await api.delete(`/users/${id}`)
      return response.data
    } catch (error: any) {
      console.error('Error deleting member:', error)
      throw new Error(error.response?.data?.message || 'فشل في حذف العضو')
    }
  }

  // الحصول على إحصائيات الأعضاء
  async getStats(): Promise<{ success: boolean; data: MemberStats }> {
    try {
      const response = await api.get('/users/stats')
      return response.data
    } catch (error: any) {
      console.error('Error fetching member stats:', error)
      throw new Error(error.response?.data?.message || 'فشل في جلب إحصائيات الأعضاء')
    }
  }

  // الحصول على اشتراكات العضو
  async getMemberSubscriptions(id: number): Promise<{ success: boolean; data: any[] }> {
    try {
      const response = await api.get(`/users/${id}/subscriptions`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member subscriptions:', error)
      return { success: true, data: [] }
    }
  }

  // الحصول على خطط التمارين للعضو
  async getMemberWorkoutPlans(id: number): Promise<{ success: boolean; data: any[] }> {
    try {
      const response = await api.get(`/users/${id}/workout-plans`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member workout plans:', error)
      return { success: true, data: [] }
    }
  }

  // الحصول على خطط التغذية للعضو
  async getMemberNutritionPlans(id: number): Promise<{ success: boolean; data: any[] }> {
    try {
      const response = await api.get(`/users/${id}/nutrition-plans`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member nutrition plans:', error)
      return { success: true, data: [] }
    }
  }

  // الحصول على بيانات اللياقة للعضو
  async getMemberFitnessData(id: number): Promise<{ success: boolean; data: any[] }> {
    try {
      const response = await api.get(`/users/${id}/fitness-data`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member fitness data:', error)
      return { success: true, data: [] }
    }
  }

  // الحصول على سجلات الحضور للعضو
  async getMemberAttendances(id: number): Promise<{ success: boolean; data: any[] }> {
    try {
      const response = await api.get(`/users/${id}/attendances`)
      return response.data
    } catch (error: any) {
      console.error('Error fetching member attendances:', error)
      return { success: true, data: [] }
    }
  }
}

export default new MembersApi()
