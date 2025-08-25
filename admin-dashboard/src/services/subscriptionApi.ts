import api from './api'

export interface Subscription {
  id: number
  user_id: number
  membership_id: number
  start_date: string
  end_date: string
  is_active: boolean
  status: string
  notes?: string
  created_at: string
  updated_at: string
  user: {
    id: number
    name: string
    email: string
  }
  membership: {
    id: number
    name: string
    price: number
    duration: number
  }
}

export interface SubscriptionFormData {
  user_id: number
  membership_id: number
  start_date: string
  end_date: string
  notes?: string
}

export interface SubscriptionStats {
  total_subscriptions: number
  active_subscriptions: number
  expired_subscriptions: number
  subscriptions_expiring_soon: number
  subscriptions_by_month: Array<{
    month: string
    count: number
  }>
}

class SubscriptionApi {
  // الحصول على قائمة الاشتراكات
  async getSubscriptions(params?: {
    status?: string
    membership_id?: number
    page?: number
  }): Promise<{ data: Subscription[]; pagination: any }> {
    try {
      // استخدام المسار التجريبي بدون مصادقة
      const response = await api.get('/test-subscriptions')
      return {
        data: response.data.subscriptions || [],
        pagination: { current_page: 1, total: response.data.subscriptions?.length || 0 }
      }
    } catch (error) {
      console.error('Error fetching subscriptions:', error)
      throw new Error('فشل في جلب قائمة الاشتراكات')
    }
  }

  // الحصول على تفاصيل اشتراك معين
  async getSubscription(id: number): Promise<Subscription> {
    try {
      const response = await api.get(`/subscriptions/${id}`)
      return response.data.data
    } catch (error) {
      console.error('Error fetching subscription:', error)
      throw new Error('فشل في جلب تفاصيل الاشتراك')
    }
  }

  // إنشاء اشتراك جديد
  async createSubscription(data: SubscriptionFormData): Promise<Subscription> {
    try {
      const response = await api.post('/subscriptions', data)
      return response.data.data
    } catch (error) {
      console.error('Error creating subscription:', error)
      throw new Error('فشل في إنشاء الاشتراك')
    }
  }

  // تحديث اشتراك موجود
  async updateSubscription(id: number, data: Partial<SubscriptionFormData>): Promise<Subscription> {
    try {
      const response = await api.put(`/subscriptions/${id}`, data)
      return response.data.data
    } catch (error) {
      console.error('Error updating subscription:', error)
      throw new Error('فشل في تحديث الاشتراك')
    }
  }

  // حذف اشتراك
  async deleteSubscription(id: number): Promise<void> {
    try {
      await api.delete(`/subscriptions/${id}`)
    } catch (error) {
      console.error('Error deleting subscription:', error)
      throw new Error('فشل في حذف الاشتراك')
    }
  }

  // تجديد اشتراك
  async renewSubscription(id: number, data: { end_date: string }): Promise<Subscription> {
    try {
      const response = await api.post(`/subscriptions/${id}/renew`, data)
      return response.data.data
    } catch (error) {
      console.error('Error renewing subscription:', error)
      throw new Error('فشل في تجديد الاشتراك')
    }
  }

  // إلغاء اشتراك
  async cancelSubscription(id: number): Promise<Subscription> {
    try {
      const response = await api.post(`/subscriptions/${id}/cancel`)
      return response.data.data
    } catch (error) {
      console.error('Error canceling subscription:', error)
      throw new Error('فشل في إلغاء الاشتراك')
    }
  }

  // الحصول على إحصائيات الاشتراكات
  async getSubscriptionStats(): Promise<SubscriptionStats> {
    try {
      // استخدام المسار التجريبي بدون مصادقة
      const response = await api.get('/test-subscriptions')
      return response.data.stats
    } catch (error) {
      console.error('Error fetching subscription stats:', error)
      throw new Error('فشل في جلب إحصائيات الاشتراكات')
    }
  }

  // الحصول على خطط العضوية المتاحة
  async getMemberships(): Promise<any[]> {
    try {
      // استخدام المسار التجريبي بدون مصادقة
      const response = await api.get('/test-subscriptions')
      return response.data.memberships || []
    } catch (error) {
      console.error('Error fetching memberships:', error)
      throw new Error('فشل في جلب خطط العضوية')
    }
  }
}

export default new SubscriptionApi()
