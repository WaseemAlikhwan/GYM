<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Attendance::query();

        // Filter based on user role
        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('coaches', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $attendance = $query->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins and coaches can create attendance records
        if (!in_array($user->role, ['admin', 'coach'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins and coaches can create attendance records.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in_time' => 'sometimes|date',
            'check_out_time' => 'sometimes|date|after:check_in_time',
            'notes' => 'nullable|string',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance records can only be created for members.'
            ], 400);
        }

        // If user is a coach, verify they are assigned to this member
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $request->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create attendance records for your assigned members.'
                ], 403);
            }
        }

        // Check if user already has attendance for today
        $existingAttendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('created_at', today())
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'User already has attendance record for today.'
            ], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'check_in_time' => $request->check_in_time ?? now(),
            'check_out_time' => $request->check_out_time,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record created successfully',
            'data' => $attendance->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $attendance->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $attendance->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $attendance->load(['user:id,name,email'])
        ]);
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'member' && $attendance->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $attendance->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $request->validate([
            'check_in_time' => 'sometimes|date',
            'check_out_time' => 'sometimes|date|after:check_in_time',
            'notes' => 'sometimes|string',
        ]);

        $attendance->update($request->only(['check_in_time', 'check_out_time', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Attendance record updated successfully',
            'data' => $attendance->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete attendance records
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    /**
     * Check in user
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Only members can check in.'
            ], 400);
        }

        // Check if user already has attendance for today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today.'
            ], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => $attendance
        ], 201);
    }

    /**
     * Check out user
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Only members can check out.'
            ], 400);
        }

        // Find today's attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No check-in record found for today.'
            ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today.'
            ], 400);
        }

        $attendance->update([
            'check_out_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful',
            'data' => $attendance
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        $query = Attendance::query();

        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('coaches', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_attendance' => $query->where('created_at', '>=', $startDate)->count(),
            'daily_attendance' => $query->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'attendance_by_member' => $this->getAttendanceByMember($user, $startDate),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get attendance by member
     */
    private function getAttendanceByMember($user, $startDate)
    {
        if ($user->role === 'admin') {
            return User::where('role', 'member')
                ->withCount(['attendances' => function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }])
                ->orderBy('attendances_count', 'desc')
                ->take(10)
                ->get();
        } elseif ($user->role === 'coach') {
            return $user->members()
                ->withCount(['attendances' => function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }])
                ->orderBy('attendances_count', 'desc')
                ->get();
        }

        return collect();
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return now()->subWeek();
            case 'month':
                return now()->subMonth();
            case 'year':
                return now()->subYear();
            default:
                return now()->subMonth();
        }
    }
}
