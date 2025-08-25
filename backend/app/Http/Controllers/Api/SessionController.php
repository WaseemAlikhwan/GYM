<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Display a listing of sessions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Session::query();

        // Filter based on user role
        if ($user->role === 'coach') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $sessions = $query->with(['user:id,name,email', 'coach:id,name,email'])
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Store a newly created session
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins and coaches can create sessions
        if (!in_array($user->role, ['admin', 'coach'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins and coaches can create sessions.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'coach_id' => 'required|exists:users,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'scheduled_at' => 'required|date|after:now',
            'duration' => 'required|integer|min:15|max:480', // 15 minutes to 8 hours
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Sessions can only be created for members.'
            ], 400);
        }

        // Check if coach is actually a coach
        $coach = User::find($request->coach_id);
        if ($coach->role !== 'coach') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coach specified.'
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
                    'message' => 'You can only create sessions for your assigned members.'
                ], 403);
            }

            // Coach can only create sessions for themselves
            if ($request->coach_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create sessions for yourself.'
                ], 403);
            }
        }

        // Check for scheduling conflicts
        $conflict = Session::where('coach_id', $request->coach_id)
            ->where('scheduled_at', '>=', $request->scheduled_at)
            ->where('scheduled_at', '<', Carbon::parse($request->scheduled_at)->addMinutes($request->duration))
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'There is a scheduling conflict with this time slot.'
            ], 400);
        }

        $session = Session::create([
            'user_id' => $request->user_id,
            'coach_id' => $request->coach_id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration' => $request->duration,
            'location' => $request->location,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully',
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified session
     */
    public function show(Session $session): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $session->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Update the specified session
     */
    public function update(Request $request, Session $session): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'type' => 'sometimes|string|max:100',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'scheduled_at' => 'sometimes|date|after:now',
            'duration' => 'sometimes|integer|min:15|max:480',
            'location' => 'sometimes|string|max:255',
            'notes' => 'sometimes|string|max:500',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
        ]);

        $session->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Session updated successfully',
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Remove the specified session
     */
    public function destroy(Session $session): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully'
        ]);
    }

    /**
     * Start session
     */
    public function startSession(Session $session): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($session->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be started. Invalid status.'
            ], 400);
        }

        $session->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session started successfully',
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Complete session
     */
    public function completeSession(Session $session): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($session->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be completed. Invalid status.'
            ], 400);
        }

        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session completed successfully',
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Cancel session
     */
    public function cancelSession(Session $session): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'coach' && $session->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($session->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed sessions cannot be cancelled.'
            ], 400);
        }

        $session->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session cancelled successfully',
            'data' => $session->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Get session statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        $query = Session::query();

        if ($user->role === 'coach') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_sessions' => $query->where('scheduled_at', '>=', $startDate)->count(),
            'scheduled_sessions' => $query->where('scheduled_at', '>=', $startDate)
                ->where('status', 'scheduled')->count(),
            'in_progress_sessions' => $query->where('scheduled_at', '>=', $startDate)
                ->where('status', 'in_progress')->count(),
            'completed_sessions' => $query->where('scheduled_at', '>=', $startDate)
                ->where('status', 'completed')->count(),
            'cancelled_sessions' => $query->where('scheduled_at', '>=', $startDate)
                ->where('status', 'cancelled')->count(),
            'sessions_by_type' => $query->where('scheduled_at', '>=', $startDate)
                ->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'upcoming_sessions' => $query->where('scheduled_at', '>=', now())
                ->where('status', 'scheduled')
                ->with(['user:id,name,email', 'coach:id,name,email'])
                ->orderBy('scheduled_at')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
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
