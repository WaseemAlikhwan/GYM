<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FitnessData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FitnessDataController extends Controller
{
    /**
     * Display a listing of fitness data records
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = FitnessData::query();

        // Filter based on user role
        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('memberCoachRelationships', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $fitnessData = $query->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $fitnessData
        ]);
    }

    /**
     * Store a newly created fitness data record
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins, coaches, and members can create fitness data records
        if (!in_array($user->role, ['admin', 'coach', 'member'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins, coaches, and members can create fitness data records.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
            'muscle_mass' => 'nullable|numeric|min:0',
            'bmi' => 'nullable|numeric|min:0',
            'waist_circumference' => 'nullable|numeric|min:0',
            'chest_circumference' => 'nullable|numeric|min:0',
            'arm_circumference' => 'nullable|numeric|min:0',
            'leg_circumference' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Fitness data records can only be created for members.'
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
                    'message' => 'You can only create fitness data records for your assigned members.'
                ], 403);
            }
        }

        // If user is a member, they can only create records for themselves
        if ($user->role === 'member' && $user->id !== $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only create fitness data records for yourself.'
            ], 403);
        }

        $fitnessData = FitnessData::create([
            'user_id' => $request->user_id,
            'weight' => $request->weight,
            'height' => $request->height,
            'body_fat_percentage' => $request->body_fat_percentage,
            'muscle_mass' => $request->muscle_mass,
            'bmi' => $request->bmi,
            'waist_circumference' => $request->waist_circumference,
            'chest_circumference' => $request->chest_circumference,
            'arm_circumference' => $request->arm_circumference,
            'leg_circumference' => $request->leg_circumference,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fitness data record created successfully',
            'data' => $fitnessData->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified fitness data record
     */
    public function show(FitnessData $fitnessData): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $fitnessData->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $fitnessData->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $fitnessData->load(['user:id,name,email'])
        ]);
    }

    /**
     * Update the specified fitness data record
     */
    public function update(Request $request, FitnessData $fitnessData): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'member' && $fitnessData->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $fitnessData->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $request->validate([
            'weight' => 'sometimes|numeric|min:0',
            'height' => 'sometimes|numeric|min:0',
            'body_fat_percentage' => 'sometimes|numeric|min:0|max:100',
            'muscle_mass' => 'sometimes|numeric|min:0',
            'bmi' => 'sometimes|numeric|min:0',
            'waist_circumference' => 'sometimes|numeric|min:0',
            'chest_circumference' => 'sometimes|numeric|min:0',
            'arm_circumference' => 'sometimes|numeric|min:0',
            'leg_circumference' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|string|max:1000',
        ]);

        $fitnessData->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fitness data record updated successfully',
            'data' => $fitnessData->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified fitness data record
     */
    public function destroy(FitnessData $fitnessData): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $fitnessData->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $fitnessData->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $fitnessData->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fitness data record deleted successfully'
        ]);
    }

    /**
     * Get fitness data statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        $query = FitnessData::query();

        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('memberCoachRelationships', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_records' => $query->where('created_at', '>=', $startDate)->count(),
            'average_weight' => $query->where('created_at', '>=', $startDate)
                ->whereNotNull('weight')->avg('weight'),
            'average_bmi' => $query->where('created_at', '>=', $startDate)
                ->whereNotNull('bmi')->avg('bmi'),
            'average_body_fat' => $query->where('created_at', '>=', $startDate)
                ->whereNotNull('body_fat_percentage')->avg('body_fat_percentage'),
            'progress_by_member' => $this->getProgressByMember($user, $startDate),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get progress by member
     */
    private function getProgressByMember($user, $startDate)
    {
        if ($user->role === 'admin') {
            return User::where('role', 'member')
                ->withCount(['fitnessData' => function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }])
                ->orderBy('fitness_data_count', 'desc')
                ->take(10)
                ->get();
        } elseif ($user->role === 'coach') {
            return $user->members()
                ->withCount(['fitnessData' => function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }])
                ->orderBy('fitness_data_count', 'desc')
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
