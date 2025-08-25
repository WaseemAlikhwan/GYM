import React from 'react'
import { NavLink, useLocation } from 'react-router-dom'

const Sidebar: React.FC = () => {
  const location = useLocation()
  
  const menuItems = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Members', href: '/members' },
    { name: 'Coaches', href: '/coaches' },
    { name: 'Memberships', href: '/memberships' },
    { name: 'Subscriptions', href: '/subscriptions' },
    { name: 'Fitness Data', href: '/fitness-data' },
    { name: 'Workout Plans', href: '/workout-plans' },
    { name: 'Nutrition Plans', href: '/nutrition-plans' },
    { name: 'Attendance', href: '/attendance' },
    { name: 'Reports', href: '/reports' },
    { name: 'Settings', href: '/settings' },
  ]

  return (
    <aside className="w-64 bg-white shadow-sm border-r border-gray-200 min-h-screen">
      <div className="p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-6">Navigation</h2>
        <nav className="space-y-2">
          {menuItems.map((item) => (
            <NavLink
              key={item.name}
              to={item.href}
              className={({ isActive }) =>
                `block px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 ${
                  isActive
                    ? 'bg-blue-100 text-blue-900'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`
              }
            >
              {item.name}
            </NavLink>
          ))}
        </nav>
      </div>
    </aside>
  )
}

export default Sidebar
