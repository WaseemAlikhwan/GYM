import React from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './contexts/AuthContext'
import ProtectedRoute from './components/ProtectedRoute'
import AdminRoute from './components/AdminRoute'
import Layout from './components/Layout'
import Dashboard from './pages/Dashboard'
import Members from './pages/Members'
import Coaches from './pages/Coaches'
import Subscriptions from './pages/Subscriptions'
import Memberships from './pages/Memberships'
import Attendance from './pages/Attendance'
import Reports from './pages/Reports'
import Settings from './pages/Settings'
import WorkoutPlans from './pages/WorkoutPlans'
import NutritionPlans from './pages/NutritionPlans'
import FitnessData from './pages/FitnessData'
import Login from './pages/Login'
import Unauthorized from './pages/Unauthorized'

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public routes */}
          <Route path="/login" element={<Login />} />
          
          {/* Admin only routes */}
          <Route path="/" element={
            <AdminRoute>
              <Navigate to="/dashboard" replace />
            </AdminRoute>
          } />
          
          <Route path="/dashboard" element={
            <AdminRoute>
              <Layout>
                <Dashboard />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/members" element={
            <AdminRoute>
              <Layout>
                <Members />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/coaches" element={
            <AdminRoute>
              <Layout>
                <Coaches />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/subscriptions" element={
            <AdminRoute>
              <Layout>
                <Subscriptions />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/memberships" element={
            <AdminRoute>
              <Layout>
                <Memberships />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/attendance" element={
            <AdminRoute>
              <Layout>
                <Attendance />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/reports" element={
            <AdminRoute>
              <Layout>
                <Reports />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/settings" element={
            <AdminRoute>
              <Layout>
                <Settings />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/workout-plans" element={
            <AdminRoute>
              <Layout>
                <WorkoutPlans />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/nutrition-plans" element={
            <AdminRoute>
              <Layout>
                <NutritionPlans />
              </Layout>
            </AdminRoute>
          } />
          
          <Route path="/fitness-data" element={
            <AdminRoute>
              <Layout>
                <FitnessData />
              </Layout>
            </AdminRoute>
          } />
          
          {/* Unauthorized page */}
          <Route path="/unauthorized" element={<Unauthorized />} />
        </Routes>
      </Router>
    </AuthProvider>
  )
}

export default App
