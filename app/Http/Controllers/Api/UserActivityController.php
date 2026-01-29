<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserActivityController extends Controller
{
    /**
     * Get user activities
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->activities();

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('activity_type', $request->type);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Store a new activity
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'activity_type' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'metadata' => 'sometimes|array',
            'ip_address' => 'sometimes|ip',
            'user_agent' => 'sometimes|string|max:500'
        ]);

        $activity = $request->user()->activities()->create([
            'activity_type' => $request->activity_type,
            'description' => $request->description,
            'metadata' => $request->metadata ?? [],
            'ip_address' => $request->ip_address ?? $request->ip(),
            'user_agent' => $request->user_agent ?? $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => $activity,
            'message' => 'Activity logged successfully'
        ], 201);
    }
}
