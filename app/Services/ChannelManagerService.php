<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Property;
use App\Models\SyncLog;
use App\Services\iCalService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Channel Manager Service
 * 
 * Handles synchronization between HabibiStay and external booking channels
 */
class ChannelManagerService
{
    /**
     * Test connection to external channel
     */
    public function testConnection(string $channelType, string $apiKey, ?string $apiSecret = null): array
    {
        try {
            switch ($channelType) {
                case Channel::TYPE_BOOKING:
                    return $this->testBookingConnection($apiKey, $apiSecret);
                case Channel::TYPE_AIRBNB:
                    return $this->testAirbnbConnection($apiKey, $apiSecret);
                case Channel::TYPE_EXPEDIA:
                    return $this->testExpediaConnection($apiKey, $apiSecret);
                default:
                    return $this->testGenericConnection($channelType, $apiKey, $apiSecret);
            }
        } catch (\Exception $e) {
            Log::error("Channel connection test failed for {$channelType}", [
                'error' => $e->getMessage(),
                'channel_type' => $channelType
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync a specific channel
     */
    public function syncChannel(Channel $channel): array
    {
        try {
            $channel->markAsSyncing();

            $result = match($channel->type) {
                Channel::TYPE_BOOKING => $this->syncBookingChannel($channel),
                Channel::TYPE_AIRBNB => $this->syncAirbnbChannel($channel),
                Channel::TYPE_EXPEDIA => $this->syncExpediaChannel($channel),
                default => $this->syncGenericChannel($channel)
            };

            if ($result['success']) {
                $channel->markAsConnected();
                
                SyncLog::logSuccess(
                    $channel->user_id,
                    $channel->id,
                    $channel->name,
                    SyncLog::ACTION_FULL_SYNC,
                    "Successfully synced {$channel->name} channel",
                    null,
                    $result['data'] ?? null
                );
            } else {
                $channel->markAsError();
                
                SyncLog::logError(
                    $channel->user_id,
                    $channel->id,
                    $channel->name,
                    SyncLog::ACTION_FULL_SYNC,
                    "Failed to sync {$channel->name}: " . $result['message'],
                    null,
                    $result['data'] ?? null
                );
            }

            return $result;
        } catch (\Exception $e) {
            $channel->markAsError();
            
            SyncLog::logError(
                $channel->user_id,
                $channel->id,
                $channel->name,
                SyncLog::ACTION_FULL_SYNC,
                "Sync error: " . $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync property to specific channel
     */
    public function syncProperty(Property $property, Channel $channel): array
    {
        try {
            $result = match($channel->type) {
                Channel::TYPE_BOOKING => $this->syncPropertyToBooking($property, $channel),
                Channel::TYPE_AIRBNB => $this->syncPropertyToAirbnb($property, $channel),
                Channel::TYPE_EXPEDIA => $this->syncPropertyToExpedia($property, $channel),
                default => $this->syncPropertyGeneric($property, $channel)
            };

            $status = $result['success'] ? SyncLog::STATUS_SUCCESS : SyncLog::STATUS_ERROR;
            
            SyncLog::create([
                'user_id' => $property->user_id,
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'property_id' => $property->id,
                'action' => SyncLog::ACTION_PROPERTY_SYNC,
                'status' => $status,
                'message' => $result['message'],
                'details' => $result['data'] ?? null
            ]);

            return $result;
        } catch (\Exception $e) {
            SyncLog::logError(
                $property->user_id,
                $channel->id,
                $channel->name,
                SyncLog::ACTION_PROPERTY_SYNC,
                "Property sync error: " . $e->getMessage(),
                $property->id
            );

            return [
                'success' => false,
                'message' => 'Property sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Booking.com connection
     */
    private function testBookingConnection(string $apiKey, ?string $apiSecret): array
    {
        // Simulate Booking.com API test
        // In real implementation, this would make actual API calls
        
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API key is required'];
        }

        // Simulate API call delay
        usleep(500000); // 0.5 seconds

        // Mock successful connection for demo
        return [
            'success' => true,
            'message' => 'Successfully connected to Booking.com',
            'data' => [
                'account_id' => 'booking_' . substr($apiKey, 0, 8),
                'account_name' => 'HabibiStay Partner Account',
                'properties_limit' => 100,
                'api_version' => '2.1'
            ]
        ];
    }

    /**
     * Test Airbnb connection
     */
    private function testAirbnbConnection(string $apiKey, ?string $apiSecret): array
    {
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API key is required'];
        }

        // Simulate API call
        usleep(500000);

        return [
            'success' => true,
            'message' => 'Successfully connected to Airbnb',
            'data' => [
                'account_id' => 'airbnb_' . substr($apiKey, 0, 8),
                'account_name' => 'HabibiStay Host',
                'properties_limit' => 50,
                'api_version' => '1.0'
            ]
        ];
    }

    /**
     * Test Expedia connection
     */
    private function testExpediaConnection(string $apiKey, ?string $apiSecret): array
    {
        if (empty($apiKey) || empty($apiSecret)) {
            return ['success' => false, 'message' => 'Both API key and secret are required'];
        }

        // Simulate API call
        usleep(500000);

        return [
            'success' => true,
            'message' => 'Successfully connected to Expedia',
            'data' => [
                'account_id' => 'expedia_' . substr($apiKey, 0, 8),
                'account_name' => 'HabibiStay Partner',
                'properties_limit' => 200,
                'api_version' => '3.0'
            ]
        ];
    }

    /**
     * Test generic channel connection
     */
    private function testGenericConnection(string $channelType, string $apiKey, ?string $apiSecret): array
    {
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API key is required'];
        }

        // Simulate API call
        usleep(500000);

        return [
            'success' => true,
            'message' => "Successfully connected to {$channelType}",
            'data' => [
                'account_id' => $channelType . '_' . substr($apiKey, 0, 8),
                'account_name' => 'HabibiStay Account',
                'properties_limit' => 100,
                'api_version' => '1.0'
            ]
        ];
    }

    /**
     * Sync Booking.com channel
     */
    private function syncBookingChannel(Channel $channel): array
    {
        // Simulate sync process
        usleep(1000000); // 1 second

        $properties = $channel->user->properties()->active()->get();
        $syncedCount = 0;
        $errors = [];

        foreach ($properties as $property) {
            $result = $this->syncPropertyToBooking($property, $channel);
            if ($result['success']) {
                $syncedCount++;
            } else {
                $errors[] = $result['message'];
            }
        }

        return [
            'success' => empty($errors),
            'message' => "Synced {$syncedCount} properties" . (empty($errors) ? '' : ', ' . count($errors) . ' errors'),
            'data' => [
                'synced_properties' => $syncedCount,
                'total_properties' => $properties->count(),
                'errors' => $errors
            ]
        ];
    }

    /**
     * Sync Airbnb channel
     */
    private function syncAirbnbChannel(Channel $channel): array
    {
        // Similar to Booking.com sync
        usleep(1000000);

        $properties = $channel->user->properties()->active()->get();
        $syncedCount = $properties->count(); // Mock all successful

        return [
            'success' => true,
            'message' => "Successfully synced {$syncedCount} properties to Airbnb",
            'data' => [
                'synced_properties' => $syncedCount,
                'total_properties' => $properties->count()
            ]
        ];
    }

    /**
     * Sync Expedia channel
     */
    private function syncExpediaChannel(Channel $channel): array
    {
        // Similar to other syncs
        usleep(1000000);

        $properties = $channel->user->properties()->active()->get();
        $syncedCount = $properties->count();

        return [
            'success' => true,
            'message' => "Successfully synced {$syncedCount} properties to Expedia",
            'data' => [
                'synced_properties' => $syncedCount,
                'total_properties' => $properties->count()
            ]
        ];
    }

    /**
     * Sync generic channel
     */
    private function syncGenericChannel(Channel $channel): array
    {
        usleep(1000000);

        $properties = $channel->user->properties()->active()->get();
        $syncedCount = $properties->count();

        return [
            'success' => true,
            'message' => "Successfully synced {$syncedCount} properties",
            'data' => [
                'synced_properties' => $syncedCount,
                'total_properties' => $properties->count()
            ]
        ];
    }

    /**
     * Sync property to Booking.com
     */
    private function syncPropertyToBooking(Property $property, Channel $channel): array
    {
        // Mock property sync
        usleep(200000); // 0.2 seconds

        return [
            'success' => true,
            'message' => "Property '{$property->title}' synced to Booking.com",
            'data' => [
                'external_id' => 'booking_' . $property->id . '_' . time(),
                'sync_time' => now()->toISOString()
            ]
        ];
    }

    /**
     * Sync property to Airbnb
     */
    private function syncPropertyToAirbnb(Property $property, Channel $channel): array
    {
        usleep(200000);

        return [
            'success' => true,
            'message' => "Property '{$property->title}' synced to Airbnb",
            'data' => [
                'external_id' => 'airbnb_' . $property->id . '_' . time(),
                'sync_time' => now()->toISOString()
            ]
        ];
    }

    /**
     * Sync property to Expedia
     */
    private function syncPropertyToExpedia(Property $property, Channel $channel): array
    {
        usleep(200000);

        return [
            'success' => true,
            'message' => "Property '{$property->title}' synced to Expedia",
            'data' => [
                'external_id' => 'expedia_' . $property->id . '_' . time(),
                'sync_time' => now()->toISOString()
            ]
        ];
    }

    /**
     * Sync property to generic channel
     */
    private function syncPropertyGeneric(Property $property, Channel $channel): array
    {
        usleep(200000);

        return [
            'success' => true,
            'message' => "Property '{$property->title}' synced to {$channel->name}",
            'data' => [
                'external_id' => $channel->type . '_' . $property->id . '_' . time(),
                'sync_time' => now()->toISOString()
            ]
        ];
    }

    /**
     * Sync calendars for a channel (iCal integration)
     */
    public function syncChannelCalendars(Channel $channel): array
    {
        try {
            $iCalService = app(iCalService::class);
            $syncResults = [];
            $totalProperties = 0;
            $syncedProperties = 0;
            
            foreach ($channel->properties as $property) {
                $totalProperties++;
                
                // Check if property has external calendar feeds
                $externalCalendars = $property->externalCalendars;
                
                if ($externalCalendars->isEmpty()) {
                    continue;
                }
                
                $propertySyncResults = [];
                foreach ($externalCalendars as $externalCalendar) {
                    try {
                        $syncResult = $iCalService->syncExternalCalendar(
                            $property, 
                            $externalCalendar->feed_url,
                            $externalCalendar->platform
                        );
                        
                        $propertySyncResults[] = [
                            'platform' => $externalCalendar->platform,
                            'feed_url' => $externalCalendar->feed_url,
                            'success' => $syncResult['success'],
                            'events_imported' => $syncResult['events_imported'] ?? 0,
                            'message' => $syncResult['message'] ?? 'Sync completed'
                        ];
                        
                        if ($syncResult['success']) {
                            $syncedProperties++;
                        }
                        
                    } catch (\Exception $e) {
                        $propertySyncResults[] = [
                            'platform' => $externalCalendar->platform,
                            'feed_url' => $externalCalendar->feed_url,
                            'success' => false,
                            'message' => 'Sync failed: ' . $e->getMessage()
                        ];
                    }
                }
                
                $syncResults[$property->id] = [
                    'property_title' => $property->title,
                    'calendars' => $propertySyncResults
                ];
            }
            
            // Log the calendar sync
            SyncLog::create([
                'user_id' => $channel->user_id,
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'action' => SyncLog::ACTION_CALENDAR_SYNC,
                'status' => $syncedProperties > 0 ? SyncLog::STATUS_SUCCESS : SyncLog::STATUS_WARNING,
                'message' => "Calendar sync completed: {$syncedProperties}/{$totalProperties} properties synced",
                'details' => json_encode($syncResults)
            ]);
            
            return [
                'success' => true,
                'message' => "Calendar sync completed: {$syncedProperties}/{$totalProperties} properties synced",
                'data' => [
                    'total_properties' => $totalProperties,
                    'synced_properties' => $syncedProperties,
                    'sync_results' => $syncResults
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error("Channel calendar sync failed for channel {$channel->id}", [
                'error' => $e->getMessage(),
                'channel_id' => $channel->id
            ]);
            
            SyncLog::logError(
                $channel->user_id,
                $channel->id,
                $channel->name,
                SyncLog::ACTION_CALENDAR_SYNC,
                "Calendar sync error: " . $e->getMessage()
            );
            
            return [
                'success' => false,
                'message' => 'Calendar sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync calendars for all user's channels
     */
    public function syncAllChannelCalendars($userId): array
    {
        try {
            $channels = Channel::where('user_id', $userId)->connected()->get();
            $results = [];
            
            foreach ($channels as $channel) {
                $results[$channel->id] = $this->syncChannelCalendars($channel);
            }
            
            $successCount = collect($results)->where('success', true)->count();
            $totalCount = $channels->count();
            
            return [
                'success' => $successCount > 0,
                'message' => "Synced calendars for {$successCount}/{$totalCount} channels",
                'data' => [
                    'total_channels' => $totalCount,
                    'successful_syncs' => $successCount,
                    'results' => $results
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error("Bulk calendar sync failed for user {$userId}", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Bulk calendar sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get financial summary across all channels
     */
    public function getChannelFinancialSummary($userId, $startDate = null, $endDate = null): array
    {
        try {
            $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : now()->startOfMonth();
            $endDate = $endDate ? \Carbon\Carbon::parse($endDate) : now()->endOfMonth();
            
            $channels = Channel::where('user_id', $userId)->connected()->get();
            $financialData = [];
            $totalRevenue = 0;
            $totalBookings = 0;
            
            foreach ($channels as $channel) {
                // Get bookings for this channel's properties within date range
                $bookings = \App\Models\Booking::whereHas('property', function($query) use ($channel) {
                    $query->whereIn('id', $channel->properties->pluck('id'));
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'confirmed')
                ->get();
                
                $channelRevenue = $bookings->sum('total_amount');
                $channelBookings = $bookings->count();
                $avgBookingValue = $channelBookings > 0 ? $channelRevenue / $channelBookings : 0;
                
                $financialData[$channel->id] = [
                    'channel_name' => $channel->name,
                    'channel_type' => $channel->type,
                    'revenue' => $channelRevenue,
                    'bookings' => $channelBookings,
                    'avg_booking_value' => $avgBookingValue,
                    'properties_count' => $channel->properties->count()
                ];
                
                $totalRevenue += $channelRevenue;
                $totalBookings += $channelBookings;
            }
            
            return [
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString()
                    ],
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'total_bookings' => $totalBookings,
                        'avg_booking_value' => $totalBookings > 0 ? $totalRevenue / $totalBookings : 0,
                        'channels_count' => $channels->count()
                    ],
                    'channels' => $financialData
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get channel financial summary for user {$userId}", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get financial summary: ' . $e->getMessage()
            ];
        }
    }
}
