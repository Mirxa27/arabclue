<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\iCalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class iCalController extends Controller
{
    protected $iCalService;

    public function __construct(iCalService $iCalService)
    {
        $this->iCalService = $iCalService;
    }

    /**
     * Import an iCal feed for a property.
     *
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function import(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $this->authorize('update', $property);

        $result = $this->iCalService->import(
            $property, 
            $request->url, 
            $request->name ?? null
        );

        if ($result) {
            return response()->json([
                'success' => true, 
                'message' => 'iCal feed imported successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import iCal feed.'
            ], 500);
        }
    }

    /**
     * Export an iCal feed for a property.
     *
     * @param Property $property
     * @return Response
     */
    public function export(Property $property): Response
    {
        // Allow public calendar viewing
        $calendarData = $this->iCalService->export($property);

        return response($calendarData, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $property->slug . '_calendar.ics"',
        ]);
    }
    
    /**
     * Manage multiple iCal feeds for a property.
     *
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function manageFeeds(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'feeds' => 'required|array',
            'feeds.*.url' => 'required|url',
            'feeds.*.name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $this->authorize('update', $property);

        $results = $this->iCalService->manageFeeds($property, $request->feeds);

        return response()->json($results);
    }
    
    /**
     * Sync all configured iCal feeds for a property.
     *
     * @param Property $property
     * @return JsonResponse
     */
    public function syncAll(Property $property): JsonResponse
    {
        $this->authorize('update', $property);

        $results = $this->iCalService->syncAllFeeds($property);

        return response()->json($results);
    }
    
    /**
     * Get all configured iCal feeds for a property.
     *
     * @param Property $property
     * @return JsonResponse
     */
    public function getFeeds(Property $property): JsonResponse
    {
        $this->authorize('update', $property);
        
        $feeds = json_decode($property->ical_feeds ?? '[]', true);
        
        return response()->json([
            'success' => true,
            'data' => [
                'feeds' => $feeds,
                'last_sync' => $property->last_calendar_sync ? $property->last_calendar_sync->toIso8601String() : null,
                'export_url' => route('api.properties.ical-export', $property->id)
            ]
        ]);
    }
}
