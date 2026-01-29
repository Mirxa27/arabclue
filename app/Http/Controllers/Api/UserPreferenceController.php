<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserPreferenceController extends Controller
{
    /**
     * Get user preferences
     */
    public function index(Request $request): JsonResponse
    {
        $preferences = $request->user()->preferences()->get();
        
        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Store a new preference
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required',
            'type' => 'required|in:string,boolean,integer,float,array,json'
        ]);

        $preference = $request->user()->preferences()->create([
            'category' => $request->category,
            'key' => $request->key,
            'value' => $request->value,
            'type' => $request->type
        ]);

        return response()->json([
            'success' => true,
            'data' => $preference,
            'message' => 'Preference saved successfully'
        ], 201);
    }

    /**
     * Update preference
     */
    public function update(Request $request, UserPreference $preference): JsonResponse
    {
        // Ensure user owns this preference
        if ($preference->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'value' => 'required',
            'type' => 'sometimes|in:string,boolean,integer,float,array,json'
        ]);

        $preference->update($request->only(['value', 'type']));

        return response()->json([
            'success' => true,
            'data' => $preference,
            'message' => 'Preference updated successfully'
        ]);
    }

    /**
     * Delete preference
     */
    public function destroy(Request $request, UserPreference $preference): JsonResponse
    {
        // Ensure user owns this preference
        if ($preference->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $preference->delete();

        return response()->json([
            'success' => true,
            'message' => 'Preference deleted successfully'
        ]);
    }
}
