<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\gym_status_logs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GymStatusLogsController extends Controller
{
    /**
     * Display a listing of gym status logs
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can view all gym status logs
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $query = gym_status_logs::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('recorded_at', $request->date);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->with(['user:id,name,email'])
            ->orderBy('recorded_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created gym status log
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins and coaches can create gym status logs
        if (!in_array($user->role, ['admin', 'coach'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins and coaches can create gym status logs.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:open,closed,maintenance,holiday',
            'recorded_at' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $gymStatusLog = gym_status_logs::create([
            'user_id' => $user->id,
            'status' => $request->status,
            'recorded_at' => $request->recorded_at ?? now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gym status log created successfully',
            'data' => $gymStatusLog->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified gym status log
     */
    public function show(gym_status_logs $gym_status_log): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can view specific gym status logs
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $gym_status_log->load(['user:id,name,email'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(gym_status_logs $gym_status_logs)
    {
        //
    }

    /**
     * Update the specified gym status log
     */
    public function update(Request $request, gym_status_logs $gym_status_log): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can update gym status logs
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:open,closed,maintenance,holiday',
            'recorded_at' => 'sometimes|date',
            'notes' => 'sometimes|string',
        ]);

        $gym_status_log->update($request->only(['status', 'recorded_at', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Gym status log updated successfully',
            'data' => $gym_status_log->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified gym status log
     */
    public function destroy(gym_status_logs $gym_status_log): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete gym status logs
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $gym_status_log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gym status log deleted successfully'
        ]);
    }

    /**
     * Get current gym status
     */
    public function getCurrentStatus(): JsonResponse
    {
        $currentStatus = gym_status_logs::latest('recorded_at')->first();

        if (!$currentStatus) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'unknown',
                    'message' => 'No status information available'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $currentStatus->status,
                'recorded_at' => $currentStatus->recorded_at,
                'notes' => $currentStatus->notes,
                'recorded_by' => $currentStatus->user->name ?? 'Unknown'
            ]
        ]);
    }

    /**
     * Get gym status history
     */
    public function getStatusHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can view gym status history
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $query = gym_status_logs::query();

        // Apply date filters
        if ($request->has('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        $history = $query->with(['user:id,name,email'])
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get gym status statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can view gym status statistics
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        
        $stats = [
            'total_logs' => gym_status_logs::where('recorded_at', '>=', $startDate)->count(),
            'status_breakdown' => gym_status_logs::where('recorded_at', '>=', $startDate)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'logs_by_day' => gym_status_logs::where('recorded_at', '>=', $startDate)
                ->selectRaw('DATE(recorded_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'most_active_users' => gym_status_logs::where('recorded_at', '>=', $startDate)
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->with('user:id,name')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get gym operating hours
     */
    public function getOperatingHours(): JsonResponse
    {
        // This would typically come from a configuration or settings table
        // For now, returning default gym hours
        $operatingHours = [
            'monday' => ['open' => '06:00', 'close' => '22:00'],
            'tuesday' => ['open' => '06:00', 'close' => '22:00'],
            'wednesday' => ['open' => '06:00', 'close' => '22:00'],
            'thursday' => ['open' => '06:00', 'close' => '22:00'],
            'friday' => ['open' => '06:00', 'close' => '22:00'],
            'saturday' => ['open' => '08:00', 'close' => '20:00'],
            'sunday' => ['open' => '08:00', 'close' => '20:00'],
        ];

        return response()->json([
            'success' => true,
            'data' => $operatingHours
        ]);
    }

    /**
     * Check if gym is currently open
     */
    public function checkIfOpen(): JsonResponse
    {
        $currentStatus = gym_status_logs::latest('recorded_at')->first();
        
        if (!$currentStatus) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is_open' => false,
                    'message' => 'No status information available'
                ]
            ]);
        }

        $isOpen = $currentStatus->status === 'open';
        $currentTime = now();
        $dayOfWeek = strtolower($currentTime->format('l'));
        
        // Get operating hours for current day
        $operatingHours = $this->getOperatingHours();
        $dayHours = $operatingHours['data'][$dayOfWeek] ?? null;
        
        $withinHours = false;
        if ($dayHours) {
            $openTime = Carbon::parse($dayHours['open']);
            $closeTime = Carbon::parse($dayHours['close']);
            $currentTimeOnly = Carbon::parse($currentTime->format('H:i'));
            
            $withinHours = $currentTimeOnly->between($openTime, $closeTime);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_open' => $isOpen && $withinHours,
                'status' => $currentStatus->status,
                'within_operating_hours' => $withinHours,
                'current_time' => $currentTime->format('H:i'),
                'day_of_week' => $dayOfWeek,
                'operating_hours' => $dayHours
            ]
        ]);
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
