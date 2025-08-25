<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Payment::query();

        // Filter based on user role
        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('memberCoachRelationships', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins and coaches can create payments
        if (!in_array($user->role, ['admin', 'coach'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins and coaches can create payments.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:100',
            'status' => 'required|in:pending,completed,failed,refunded',
            'description' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Payments can only be created for members.'
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
                    'message' => 'You can only create payments for your assigned members.'
                ], 403);
            }
        }

        $payment = Payment::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'description' => $request->description,
            'transaction_id' => $request->transaction_id,
            'payment_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => $payment->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $payment->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $payment->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payment->load(['user:id,name,email'])
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can update payments
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'description' => 'sometimes|string|max:500',
            'transaction_id' => 'sometimes|string|max:255',
        ]);

        $payment->update($request->only(['status', 'description', 'transaction_id']));

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete payments
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Get payment statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        $query = Payment::query();

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
            'total_payments' => $query->where('created_at', '>=', $startDate)->count(),
            'total_amount' => $query->where('created_at', '>=', $startDate)->sum('amount'),
            'completed_payments' => $query->where('created_at', '>=', $startDate)
                ->where('status', 'completed')->count(),
            'completed_amount' => $query->where('created_at', '>=', $startDate)
                ->where('status', 'completed')->sum('amount'),
            'pending_payments' => $query->where('created_at', '>=', $startDate)
                ->where('status', 'pending')->count(),
            'failed_payments' => $query->where('created_at', '>=', $startDate)
                ->where('status', 'failed')->count(),
            'payments_by_method' => $query->where('created_at', '>=', $startDate)
                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('payment_method')
                ->get(),
            'payments_by_day' => $query->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('date')
                ->orderBy('date')
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
