<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Subscription::query();

        // Filter based on user role
        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('end_date', '>=', now());
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        if ($request->has('membership_id')) {
            $query->where('membership_id', $request->membership_id);
        }

        $subscriptions = $query->with(['user:id,name,email', 'membership:id,name,price,duration'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $subscriptions
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
     * Store a newly created subscription
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can create subscriptions
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins can create subscriptions.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'membership_id' => 'required|exists:memberships,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Subscriptions can only be created for members.'
            ], 400);
        }

        // Check if user already has an active subscription
        $activeSubscription = Subscription::where('user_id', $request->user_id)
            ->where('end_date', '>=', now())
            ->first();

        if ($activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription.'
            ], 400);
        }

        $subscription = Subscription::create([
            'user_id' => $request->user_id,
            'membership_id' => $request->membership_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'data' => $subscription->load(['user:id,name,email', 'membership:id,name,price,duration'])
        ], 201);
    }

    /**
     * Display the specified subscription
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $subscription->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription->load(['user:id,name,email', 'membership:id,name,price,duration'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(subscription $subscription)
    {
        //
    }

    /**
     * Update the specified subscription
     */
    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can update subscriptions
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|string',
        ]);

        $subscription->update($request->only(['start_date', 'end_date', 'is_active', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => $subscription->load(['user:id,name,email', 'membership:id,name,price,duration'])
        ]);
    }

    /**
     * Remove the specified subscription
     */
    public function destroy(Subscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete subscriptions
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription deleted successfully'
        ]);
    }

    /**
     * Get available memberships
     */
    public function getMemberships(): JsonResponse
    {
        $memberships = Membership::where('is_active', true)
            ->select('id', 'name', 'description', 'price', 'duration', 'features')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    /**
     * Get user's current subscription
     */
    public function getCurrentSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is only for members.'
            ], 400);
        }

        $subscription = Subscription::where('user_id', $user->id)
            ->where('end_date', '>=', now())
            ->with(['membership:id,name,price,duration,features'])
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active subscription found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription
        ]);
    }

    /**
     * Get subscription history for user
     */
    public function getSubscriptionHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is only for members.'
            ], 400);
        }

        $subscriptions = Subscription::where('user_id', $user->id)
            ->with(['membership:id,name,price,duration'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * Renew subscription
     */
    public function renewSubscription(Request $request, Subscription $subscription): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can renew subscriptions
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'extension_days' => 'required|integer|min:1',
        ]);

        $extensionDays = $request->extension_days;
        $newEndDate = Carbon::parse($subscription->end_date)->addDays($extensionDays);

        $subscription->update([
            'end_date' => $newEndDate,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription renewed successfully',
            'data' => $subscription->load(['user:id,name,email', 'membership:id,name,price,duration'])
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request, Subscription $subscription): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can cancel subscriptions
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $subscription->update([
            'is_active' => false,
            'end_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'data' => $subscription->load(['user:id,name,email', 'membership:id,name,price,duration'])
        ]);
    }

    /**
     * Get subscription statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('end_date', '>=', now())->count(),
            'expired_subscriptions' => Subscription::where('end_date', '<', now())->count(),
            'subscriptions_expiring_soon' => Subscription::where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now())
                ->count(),
            'total_revenue' => Subscription::where('end_date', '>=', now())->sum('price'),
            'subscriptions_by_month' => $this->getSubscriptionsByMonth(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get subscriptions by month
     */
    private function getSubscriptionsByMonth()
    {
        return Subscription::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'month' => date('F', mktime(0, 0, 0, $item->month, 1)),
                    'count' => $item->count
                ];
            });
    }
}
