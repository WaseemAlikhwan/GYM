import React from 'react'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Area, AreaChart } from 'recharts'

interface GrowthData {
  month: string
  members: number
  coaches: number
  revenue: number
}

interface GrowthChartProps {
  data: GrowthData[]
}

const GrowthChart: React.FC<GrowthChartProps> = ({ data }) => {
  return (
    <div className="h-64">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis 
            dataKey="month" 
            tick={{ fontSize: 12 }}
            axisLine={false}
            tickLine={false}
          />
          <YAxis 
            tick={{ fontSize: 12 }}
            axisLine={false}
            tickLine={false}
          />
          <Tooltip 
            contentStyle={{
              backgroundColor: 'white',
              border: '1px solid #e5e7eb',
              borderRadius: '8px',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
            }}
            formatter={(value: number, name: string) => [
              name === 'members' ? `${value} عضو` : 
              name === 'coaches' ? `${value} مدرب` : 
              `$${value.toLocaleString()}`,
              name === 'members' ? 'الأعضاء' : 
              name === 'coaches' ? 'المدربين' : 'الإيرادات'
            ]}
          />
          <Area 
            type="monotone" 
            dataKey="members" 
            stackId="1"
            stroke="#3b82f6" 
            fill="#3b82f6" 
            fillOpacity={0.6}
          />
          <Area 
            type="monotone" 
            dataKey="coaches" 
            stackId="1"
            stroke="#10b981" 
            fill="#10b981" 
            fillOpacity={0.6}
          />
          <Area 
            type="monotone" 
            dataKey="revenue" 
            stackId="1"
            stroke="#f59e0b" 
            fill="#f59e0b" 
            fillOpacity={0.6}
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}

export default GrowthChart












