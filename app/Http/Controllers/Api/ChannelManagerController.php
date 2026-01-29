<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Channel;
use App\Models\Property;
use App\Models\SyncLog;
use App\Services\ChannelManagerService;

/**
 * Channel Manager Controller
 * 
 * Manages property distribution across multiple booking platforms:
 * - Channel connections (Booking.com, Airbnb, Expedia, etc.)
 * - Property synchronization
 * - Availability and pricing sync
 * - Booking import/export
 */
class ChannelManagerController extends Controller
{
    protected $channelService;

    public function __construct(ChannelManagerService $channelService)
    {
        $this->channelService = $channelService;
    }

    /**
     * Get all connected channels for the host
     */
    public function index(): JsonResponse
    {
        try {
            $hostId = auth()->id();
            
            $channels = Channel::where('user_id', $hostId)
                ->with(['properties'])
                ->get()
                ->map(function ($channel) {
                    return [
                        'id' => $channel->id,
                        'name' => $channel->name,
                        'type' => $channel->type,
                        'status' => $channel->status,
                        'auto_sync' => $channel->auto_sync,
                        'last_sync_at' => $channel->last_sync_at,
                        'properties_count' => $channel->properties->count(),
                        'created_at' => $channel->created_at
                    ];
                });

            $stats = [
                'connected_channels' => $channels->count(),
                'active_channels' => $channels->where('status', 'connected')->count(),
                'last_sync' => $channels->max('last_sync_at'),
                'sync_issues' => SyncLog::where('user_id', $hostId)
                    ->where('status', 'error')
                    ->where('created_at', '>=', now()->subDay())
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $channels,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading channels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Connect a new channel
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:booking,airbnb,expedia,agoda,vrbo',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'auto_sync' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hostId = auth()->id();

            // Check if channel type already exists for this host
            $existingChannel = Channel::where('user_id', $hostId)
                ->where('type', $request->type)
                ->first();

            if ($existingChannel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Channel of this type already connected'
                ], 409);
            }

            // Test connection before saving
            $connectionTest = $this->channelService->testConnection(
                $request->type,
                $request->api_key,
                $request->api_secret
            );

            if (!$connectionTest['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed: ' . $connectionTest['message']
                ], 400);
            }

            $channel = Channel::create([
                'user_id' => $hostId,
                'type' => $request->type,
                'name' => $request->name,
                'api_key' => encrypt($request->api_key),
                'api_secret' => $request->api_secret ? encrypt($request->api_secret) : null,
                'auto_sync' => $request->boolean('auto_sync', true),
                'status' => 'connected',
                'last_sync_at' => now()
            ]);

            // Log the connection
            SyncLog::create([
                'user_id' => $hostId,
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'action' => 'channel_connected',
                'status' => 'success',
                'message' => "Successfully connected {$channel->name} channel"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Channel connected successfully',
                'data' => $channel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error connecting channel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all channels
     */
    public function syncAll(): JsonResponse
    {
        try {
            $hostId = auth()->id();
            
            $channels = Channel::where('user_id', $hostId)
                ->where('status', 'connected')
                ->get();

            $syncResults = [];
            
            foreach ($channels as $channel) {
                $result = $this->channelService->syncChannel($channel);
                $syncResults[] = [
                    'channel_id' => $channel->id,
                    'channel_name' => $channel->name,
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
            }

            $successCount = collect($syncResults)->where('success', true)->count();
            $totalCount = count($syncResults);

            return response()->json([
                'success' => true,
                'message' => "Sync completed: {$successCount}/{$totalCount} channels synced successfully",
                'results' => $syncResults
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing channels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync specific channel
     */
    public function syncChannel(Channel $channel): JsonResponse
    {
        try {
            // Verify channel belongs to authenticated host
            if ($channel->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $result = $this->channelService->syncChannel($channel);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing channel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs(Request $request): JsonResponse
    {
        try {
            $hostId = auth()->id();
            $limit = $request->get('limit', 20);

            $logs = SyncLog::where('user_id', $hostId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'channel_name' => $log->channel_name,
                        'action' => $log->action,
                        'status' => $log->status,
                        'message' => $log->message,
                        'created_at' => $log->created_at,
                        'details' => $log->details
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading sync logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update channel configuration
     */
    public function update(Request $request, Channel $channel): JsonResponse
    {
        // Verify channel belongs to authenticated host
        if ($channel->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'api_key' => 'sometimes|string',
            'api_secret' => 'sometimes|string',
            'auto_sync' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:connected,disconnected,error'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('api_key')) {
                // Test new credentials if provided
                $connectionTest = $this->channelService->testConnection(
                    $channel->type,
                    $request->api_key,
                    $request->api_secret ?? decrypt($channel->api_secret)
                );

                if (!$connectionTest['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Connection test failed: ' . $connectionTest['message']
                    ], 400);
                }

                $updateData['api_key'] = encrypt($request->api_key);
            }

            if ($request->has('api_secret')) {
                $updateData['api_secret'] = encrypt($request->api_secret);
            }

            if ($request->has('auto_sync')) {
                $updateData['auto_sync'] = $request->boolean('auto_sync');
            }

            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            $channel->update($updateData);

            // Log the update
            SyncLog::create([
                'user_id' => auth()->id(),
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'action' => 'channel_updated',
                'status' => 'success',
                'message' => "Channel configuration updated"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Channel updated successfully',
                'data' => $channel->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating channel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect/delete a channel
     */
    public function destroy(Channel $channel): JsonResponse
    {
        // Verify channel belongs to authenticated host
        if ($channel->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $channelName = $channel->name;
            
            // Log the disconnection
            SyncLog::create([
                'user_id' => auth()->id(),
                'channel_id' => $channel->id,
                'channel_name' => $channelName,
                'action' => 'channel_disconnected',
                'status' => 'success',
                'message' => "Channel {$channelName} disconnected"
            ]);

            $channel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Channel disconnected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error disconnecting channel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available channel types
     */
    public function getAvailableChannels(): JsonResponse
    {
        $availableChannels = [
            [
                'type' => 'booking',
                'name' => 'Booking.com',
                'description' => 'World\'s largest accommodation booking platform',
                'logo' => '/images/channels/booking.png',
                'features' => ['Instant booking', 'Global reach', 'Multiple languages']
            ],
            [
                'type' => 'airbnb',
                'name' => 'Airbnb',
                'description' => 'Leading vacation rental marketplace',
                'logo' => '/images/channels/airbnb.png',
                'features' => ['Experience bookings', 'Host protection', 'Community reviews']
            ],
            [
                'type' => 'expedia',
                'name' => 'Expedia',
                'description' => 'Major online travel booking platform',
                'logo' => '/images/channels/expedia.png',
                'features' => ['Package deals', 'Loyalty program', 'Business travel']
            ],
            [
                'type' => 'agoda',
                'name' => 'Agoda',
                'description' => 'Asia-focused online travel booking platform',
                'logo' => '/images/channels/agoda.png',
                'features' => ['Asia expertise', 'Local partnerships', 'Mobile-first']
            ],
            [
                'type' => 'vrbo',
                'name' => 'VRBO',
                'description' => 'Vacation rental by owner platform',
                'logo' => '/images/channels/vrbo.png',
                'features' => ['Whole home rentals', 'Family focus', 'Flexible cancellation']
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $availableChannels
        ]);
    }

    /**
     * Sync calendars for a specific channel
     */
    public function syncChannelCalendars(Channel $channel): JsonResponse
    {
        try {
            // Verify channel belongs to authenticated host
            if ($channel->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $result = $this->channelService->syncChannelCalendars($channel);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing calendars: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync calendars for all user's channels
     */
    public function syncAllCalendars(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $result = $this->channelService->syncAllChannelCalendars($userId);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing all calendars: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial summary across all channels
     */
    public function getFinancialSummary(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $result = $this->channelService->getChannelFinancialSummary($userId, $startDate, $endDate);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Financial summary retrieved successfully',
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting financial summary: ' . $e->getMessage()
            ], 500);
        }
    }
}
