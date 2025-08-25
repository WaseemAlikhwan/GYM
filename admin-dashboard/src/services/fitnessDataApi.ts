import api from './api';

export interface FitnessData {
  id: number;
  user_id: number;
  user_name?: string;
  user_email?: string;
  weight: number;
  height: number;
  bmi?: number;
  fat_percent?: number;
  muscle_mass?: number;
  body_fat_percentage?: number;
  waist_circumference?: number;
  chest_circumference?: number;
  arm_circumference?: number;
  leg_circumference?: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface FitnessDataFormData {
  user_id: number;
  weight: number;
  height: number;
  bmi?: number;
  fat_percent?: number;
  muscle_mass?: number;
  body_fat_percentage?: number;
  waist_circumference?: number;
  chest_circumference?: number;
  arm_circumference?: number;
  leg_circumference?: number;
  notes?: string;
}

export interface FitnessDataStats {
  total_records: number;
  average_weight: number;
  average_bmi: number;
  average_body_fat: number;
  progress_by_member: any[];
}

export interface FitnessDataFilters {
  user_id?: number;
  start_date?: string;
  end_date?: string;
  period?: 'week' | 'month' | 'year';
}

// Mock data management for development without authentication
let localFitnessData: FitnessData[] = [];
let isLocalDataInitialized = false;

class FitnessDataApi {
  private async initializeLocalData() {
    if (isLocalDataInitialized) return localFitnessData;
    
    try {
      const response = await api.get('/test-fitness-data');
      localFitnessData = response.data.fitnessData || [];
      isLocalDataInitialized = true;
      return localFitnessData;
    } catch (error) {
      console.error('Error initializing local data:', error);
      localFitnessData = [];
      isLocalDataInitialized = true;
      return localFitnessData;
    }
  }

  // الحصول على قائمة بيانات اللياقة البدنية
  async getFitnessData(filters?: FitnessDataFilters): Promise<FitnessData[]> {
    try {
      await this.initializeLocalData();
      return localFitnessData;
    } catch (error) {
      console.error('Error fetching fitness data:', error);
      throw new Error('فشل في جلب بيانات اللياقة البدنية');
    }
  }

  // الحصول على بيانات لياقة بدنية لمستخدم معين
  async getMemberFitnessData(memberId: number): Promise<FitnessData[]> {
    try {
      await this.initializeLocalData();
      return localFitnessData.filter(data => data.user_id === memberId);
    } catch (error) {
      console.error('Error fetching member fitness data:', error);
      throw new Error('فشل في جلب بيانات اللياقة البدنية للعضو');
    }
  }

  // إنشاء بيانات لياقة بدنية جديدة
  async createFitnessData(data: FitnessDataFormData): Promise<FitnessData> {
    try {
      await this.initializeLocalData();
      
      const newData: FitnessData = {
        id: Date.now(),
        user_id: data.user_id,
        user_name: `العضو ${data.user_id}`,
        user_email: 'user@example.com',
        weight: data.weight,
        height: data.height,
        bmi: data.bmi || this.calculateBMI(data.weight, data.height),
        fat_percent: data.fat_percent,
        muscle_mass: data.muscle_mass,
        body_fat_percentage: data.body_fat_percentage,
        waist_circumference: data.waist_circumference,
        chest_circumference: data.chest_circumference,
        arm_circumference: data.arm_circumference,
        leg_circumference: data.leg_circumference,
        notes: data.notes,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };
      
      localFitnessData.unshift(newData);
      return newData;
    } catch (error) {
      console.error('Error creating fitness data:', error);
      throw new Error('فشل في إنشاء بيانات اللياقة البدنية');
    }
  }

  // تحديث بيانات لياقة بدنية
  async updateFitnessData(id: number, data: Partial<FitnessDataFormData>): Promise<FitnessData> {
    try {
      await this.initializeLocalData();
      
      const index = localFitnessData.findIndex(item => item.id === id);
      if (index === -1) {
        throw new Error('بيانات اللياقة البدنية غير موجودة');
      }
      
      const updatedData = {
        ...localFitnessData[index],
        ...data,
        bmi: data.weight && data.height ? this.calculateBMI(data.weight, data.height) : localFitnessData[index].bmi,
        updated_at: new Date().toISOString()
      };
      
      localFitnessData[index] = updatedData;
      return updatedData;
    } catch (error) {
      console.error('Error updating fitness data:', error);
      throw new Error('فشل في تحديث بيانات اللياقة البدنية');
    }
  }

  // حذف بيانات لياقة بدنية
  async deleteFitnessData(id: number): Promise<void> {
    try {
      await this.initializeLocalData();
      
      const index = localFitnessData.findIndex(item => item.id === id);
      if (index === -1) {
        throw new Error('بيانات اللياقة البدنية غير موجودة');
      }
      
      localFitnessData.splice(index, 1);
    } catch (error) {
      console.error('Error deleting fitness data:', error);
      throw new Error('فشل في حذف بيانات اللياقة البدنية');
    }
  }

  // الحصول على إحصائيات بيانات اللياقة البدنية
  async getFitnessDataStats(period: 'week' | 'month' | 'year' = 'month'): Promise<FitnessDataStats> {
    try {
      await this.initializeLocalData();
      
      const stats: FitnessDataStats = {
        total_records: localFitnessData.length,
        average_weight: localFitnessData.length > 0 ? 
          localFitnessData.reduce((sum, data) => sum + data.weight, 0) / localFitnessData.length : 0,
        average_bmi: localFitnessData.filter(data => data.bmi).length > 0 ?
          localFitnessData.filter(data => data.bmi).reduce((sum, data) => sum + (data.bmi || 0), 0) / 
          localFitnessData.filter(data => data.bmi).length : 0,
        average_body_fat: localFitnessData.filter(data => data.fat_percent).length > 0 ?
          localFitnessData.filter(data => data.fat_percent).reduce((sum, data) => sum + (data.fat_percent || 0), 0) / 
          localFitnessData.filter(data => data.fat_percent).length : 0,
        progress_by_member: []
      };
      
      return stats;
    } catch (error) {
      console.error('Error fetching fitness data stats:', error);
      throw new Error('فشل في جلب إحصائيات بيانات اللياقة البدنية');
    }
  }

  // الحصول على تقدم عضو معين
  async getMemberProgress(memberId: number): Promise<FitnessData[]> {
    try {
      await this.initializeLocalData();
      return localFitnessData.filter(data => data.user_id === memberId);
    } catch (error) {
      console.error('Error fetching member progress:', error);
      throw new Error('فشل في جلب تقدم العضو');
    }
  }

  // حساب BMI تلقائياً
  calculateBMI(weight: number, height: number): number {
    if (height <= 0) return 0;
    return Math.round((weight / Math.pow(height / 100, 2)) * 10) / 10;
  }

  // تصنيف BMI
  getBMICategory(bmi: number): { category: string; color: string; description: string } {
    if (bmi < 18.5) {
      return { category: 'نقص وزن', color: 'text-blue-600', description: 'وزن أقل من الطبيعي' };
    } else if (bmi < 25) {
      return { category: 'وزن طبيعي', color: 'text-green-600', description: 'وزن مثالي' };
    } else if (bmi < 30) {
      return { category: 'وزن زائد', color: 'text-yellow-600', description: 'وزن أعلى من الطبيعي' };
    } else {
      return { category: 'سمنة', color: 'text-red-600', description: 'وزن مرتفع جداً' };
    }
  }
}

export default new FitnessDataApi();
