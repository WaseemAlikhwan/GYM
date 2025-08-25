import api from './api'

// واجهات البيانات
export interface Membership {
  id: number
  name: string
  description: string
  price: number
  duration_days: number
  has_coach: boolean
  has_workout_plan: boolean
  has_nutrition_plan: boolean
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface MembershipFormData {
  name: string
  description: string
  price: number
  duration_days: number
  has_coach: boolean
  has_workout_plan: boolean
  has_nutrition_plan: boolean
  is_active: boolean
}

export interface MembershipStats {
  total_memberships: number
  active_memberships: number
  total_subscriptions: number
  active_subscriptions: number
  revenue_by_membership: Array<{
    membership_name: string
    subscriptions_count: number
    total_revenue: number
  }>
}

// متغير محلي لتخزين البيانات في الذاكرة (للتطوير فقط)
let localMemberships: Membership[] = []
let isLocalDataInitialized = false

class MembershipApi {
  // تهيئة البيانات المحلية
  private async initializeLocalData() {
    if (isLocalDataInitialized) return localMemberships
    
    try {
      const response = await api.get('/test-memberships')
      localMemberships = response.data.memberships || []
      isLocalDataInitialized = true
      return localMemberships
    } catch (error) {
      console.error('Error initializing local data:', error)
      localMemberships = []
      isLocalDataInitialized = true
      return localMemberships
    }
  }

  // الحصول على قائمة خطط العضوية
  async getMemberships(): Promise<Membership[]> {
    try {
      await this.initializeLocalData()
      return localMemberships
    } catch (error) {
      console.error('Error fetching memberships:', error)
      throw new Error('فشل في جلب قائمة خطط العضوية')
    }
  }

  // الحصول على تفاصيل خطة عضوية معينة
  async getMembership(id: number): Promise<Membership> {
    try {
      await this.initializeLocalData()
      const membership = localMemberships.find(m => m.id === id)
      if (!membership) {
        throw new Error('خطة العضوية غير موجودة')
      }
      return membership
    } catch (error) {
      console.error('Error fetching membership:', error)
      throw new Error('فشل في جلب تفاصيل خطة العضوية')
    }
  }

  // إنشاء خطة عضوية جديدة
  async createMembership(data: MembershipFormData): Promise<Membership> {
    try {
      await this.initializeLocalData()
      
      const newMembership: Membership = {
        id: Date.now(), // ID مؤقت
        ...data,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      }
      
      // إضافة إلى القائمة المحلية
      localMemberships.push(newMembership)
      
      console.log('Creating membership:', newMembership)
      console.log('Total memberships after creation:', localMemberships.length)
      
      return newMembership
    } catch (error) {
      console.error('Error creating membership:', error)
      throw new Error('فشل في إنشاء خطة العضوية')
    }
  }

  // تحديث خطة عضوية موجودة
  async updateMembership(id: number, data: Partial<MembershipFormData>): Promise<Membership> {
    try {
      await this.initializeLocalData()
      
      const index = localMemberships.findIndex(m => m.id === id)
      if (index === -1) {
        throw new Error('خطة العضوية غير موجودة')
      }
      
      const updatedMembership: Membership = {
        ...localMemberships[index],
        ...data,
        updated_at: new Date().toISOString()
      }
      
      // تحديث في القائمة المحلية
      localMemberships[index] = updatedMembership
      
      console.log('Updating membership:', updatedMembership)
      
      return updatedMembership
    } catch (error) {
      console.error('Error updating membership:', error)
      throw new Error('فشل في تحديث خطة العضوية')
    }
  }

  // حذف خطة عضوية
  async deleteMembership(id: number): Promise<void> {
    try {
      await this.initializeLocalData()
      
      const index = localMemberships.findIndex(m => m.id === id)
      if (index === -1) {
        throw new Error('خطة العضوية غير موجودة')
      }
      
      // حذف من القائمة المحلية
      localMemberships.splice(index, 1)
      
      console.log('Deleting membership with ID:', id)
      console.log('Total memberships after deletion:', localMemberships.length)
      
      return Promise.resolve()
    } catch (error) {
      console.error('Error deleting membership:', error)
      throw new Error('فشل في حذف خطة العضوية')
    }
  }

  // الحصول على إحصائيات خطط العضوية
  async getMembershipStats(): Promise<MembershipStats> {
    try {
      await this.initializeLocalData()
      
      // حساب الإحصائيات من البيانات المحلية
      const total_memberships = localMemberships.length
      const active_memberships = localMemberships.filter(m => m.is_active).length
      
      // محاكاة بيانات الاشتراكات
      const total_subscriptions = Math.floor(total_memberships * 2.5) // متوسط 2.5 اشتراك لكل خطة
      const active_subscriptions = Math.floor(active_memberships * 2.5)
      
      const revenue_by_membership = localMemberships.map(membership => ({
        membership_name: membership.name,
        subscriptions_count: Math.floor(Math.random() * 5) + 1, // 1-5 اشتراكات
        total_revenue: membership.price * (Math.floor(Math.random() * 5) + 1)
      }))
      
      return {
        total_memberships,
        active_memberships,
        total_subscriptions,
        active_subscriptions,
        revenue_by_membership
      }
    } catch (error) {
      console.error('Error fetching membership stats:', error)
      throw new Error('فشل في جلب إحصائيات خطط العضوية')
    }
  }

  // الحصول على خطط العضوية الشائعة
  async getPopularMemberships(): Promise<Membership[]> {
    try {
      await this.initializeLocalData()
      return localMemberships.slice(0, 3) // أول 3 خطط
    } catch (error) {
      console.error('Error fetching popular memberships:', error)
      throw new Error('فشل في جلب خطط العضوية الشائعة')
    }
  }

  // تحديث جماعي لخطط العضوية
  async bulkUpdateMemberships(data: { ids: number[], updates: Partial<MembershipFormData> }): Promise<void> {
    try {
      await this.initializeLocalData()
      
      console.log('Bulk updating memberships:', data)
      
      // تحديث جميع العضويات المحددة
      data.ids.forEach(id => {
        const index = localMemberships.findIndex(m => m.id === id)
        if (index !== -1) {
          localMemberships[index] = {
            ...localMemberships[index],
            ...data.updates,
            updated_at: new Date().toISOString()
          }
        }
      })
      
      return Promise.resolve()
    } catch (error) {
      console.error('Error bulk updating memberships:', error)
      throw new Error('فشل في التحديث الجماعي لخطط العضوية')
    }
  }
}

export default new MembershipApi()
