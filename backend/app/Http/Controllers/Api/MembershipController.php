<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{
    /**
     * Display a listing of memberships
     */
    public function index(Request $request): JsonResponse
    {
        $query = Membership::query();

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $memberships = $query->orderBy('price', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    /**
     * Store a newly created membership
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can create memberships
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins can create memberships.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $membership = Membership::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
            'duration' => $request->duration,
            'features' => $request->features ? json_encode($request->features) : null,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Membership created successfully',
            'data' => $membership
        ], 201);
    }

    /**
     * Display the specified membership
     */
    public function show(Membership $membership): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $membership
        ]);
    }

    /**
     * Update the specified membership
     */
    public function update(Request $request, Membership $membership): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can update memberships
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'duration' => 'sometimes|integer|min:1',
            'features' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $membershipData = $request->all();
        
        if ($request->features) {
            $membershipData['features'] = json_encode($request->features);
        }

        $membership->update($membershipData);

        return response()->json([
            'success' => true,
            'message' => 'Membership updated successfully',
            'data' => $membership->fresh()
        ]);
    }

    /**
     * Remove the specified membership
     */
    public function destroy(Membership $membership): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete memberships
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Check if membership is being used by any subscriptions
        if ($membership->subscriptions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete membership that has active subscriptions.'
            ], 400);
        }

        $membership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membership deleted successfully'
        ]);
    }

    /**
     * Get active memberships
     */
    public function getActiveMemberships(): JsonResponse
    {
        $memberships = Membership::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    /**
     * Get membership statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::where('is_active', true)->count(),
            'inactive_memberships' => Membership::where('is_active', false)->count(),
            'memberships_by_type' => Membership::select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'total_subscriptions' => \App\Models\Subscription::count(),
            'active_subscriptions' => \App\Models\Subscription::where('is_active', true)->count(),
            'revenue_by_membership' => Membership::withCount(['subscriptions' => function($query) {
                $query->where('is_active', true);
            }])->get()->map(function($membership) {
                return [
                    'name' => $membership->name,
                    'subscriptions_count' => $membership->subscriptions_count,
                    'total_revenue' => $membership->subscriptions_count * $membership->price
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
