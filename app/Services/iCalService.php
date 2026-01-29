<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyCalendar;
use Carbon\Carbon;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sabre\VObject;

class iCalService
{
    /**
     * Import and sync bookings from an external iCal URL.
     *
     * @param Property $property The property to sync bookings for.
     * @param string $url The iCal feed URL.
     * @param string|null $source_name Optional identifier for the source.
     * @return bool True on success, false on failure.
     */
    public function import(Property $property, string $url, ?string $source_name = null): bool
    {
        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::error("Failed to fetch iCal URL for Property ID {$property->id}. Status: " . $response->status(), [
                    'url' => $url,
                ]);
                return false;
            }

            $icalContent = $response->body();
            $calendar = VObject\Reader::read($icalContent);

            // Generate or use source name
            $source = $source_name ?: 'external_' . Str::slug(parse_url($url, PHP_URL_HOST));
            
            // Clear existing calendar entries from this source before importing new ones
            PropertyCalendar::where('property_id', $property->id)
                ->where('source', $source)
                ->delete();

            $importedCount = 0;
            $skippedCount = 0;

            if (!empty($calendar->VEVENT)) {
                foreach ($calendar->VEVENT as $event) {
                    // Skip events without DTSTART or DTEND
                    if (empty($event->DTSTART) || empty($event->DTEND)) {
                        $skippedCount++;
                        continue;
                    }

                    // Parse dates
                    $startDate = Carbon::parse($event->DTSTART->getValue());
                    $endDate = Carbon::parse($event->DTEND->getValue());
                    
                    // Skip past events
                    if ($endDate->isPast()) {
                        $skippedCount++;
                        continue;
                    }

                    // Extract unique ID from event or generate one
                    $uid = $event->UID ? (string)$event->UID : Str::uuid()->toString();
                    
                    // Extract summary/title (or use default)
                    $summary = $event->SUMMARY ? (string)$event->SUMMARY : 'External Booking';
                    
                    // Create calendar entries for each date in the range
                    $current = $startDate->copy();
                    while ($current->lt($endDate)) {
                        PropertyCalendar::updateOrCreate([
                            'property_id' => $property->id,
                            'date' => $current->format('Y-m-d'),
                        ], [
                            'status' => PropertyCalendar::STATUS_BLOCKED,
                            'title' => $summary,
                            'source' => $source,
                            'external_id' => $uid,
                            'notes' => $event->DESCRIPTION ? (string)$event->DESCRIPTION : null,
                        ]);
                        $current->addDay();
                    }
                    
                    $importedCount++;
                }
            }

            // Update property's last sync time
            $property->last_calendar_sync = now();
            $property->save();

            // Log the results
            Log::info("iCal sync completed for Property #{$property->id}", [
                'property_id' => $property->id,
                'imported_events' => $importedCount,
                'skipped_events' => $skippedCount,
                'source' => $source
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error syncing iCal for Property ID {$property->id}: " . $e->getMessage(), [
                'url' => $url,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Export property availability as iCal feed.
     *
     * @param Property $property The property to export calendar for.
     * @return string The iCal content.
     */
    public function export(Property $property): string
    {
        // Create calendar instance
        $calendar = new Calendar();

        // Get all bookings for this property
        $bookings = Booking::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        // Get all calendar blocks for this property
        $calendarBlocks = PropertyCalendar::where('property_id', $property->id)
            ->where('status', PropertyCalendar::STATUS_BLOCKED)
            ->get();

        // Add booking events to calendar
        foreach ($bookings as $booking) {
            // Only include confirmed or pending bookings
            $event = $this->createEvent(
                $booking->id,
                "Booking #" . $booking->id,
                "Property booked from {$booking->check_in} to {$booking->check_out}",
                $booking->check_in,
                $booking->check_out
            );
            $calendar->addEvent($event);
        }

        // Add calendar blocks to the feed
        foreach ($calendarBlocks as $block) {
            $event = $this->createEvent(
                'block-' . $block->id,
                $block->title ?: 'Unavailable',
                $block->notes ?: 'Property unavailable during this period',
                $block->date,
                $block->date
            );
            $calendar->addEvent($event);
        }

        // Convert to iCal format
        $calendarFactory = new CalendarFactory();
        return $calendarFactory->createCalendar($calendar);
    }

    /**
     * Create an iCal event.
     *
     * @param string $uid Unique identifier for the event
     * @param string $summary Event summary/title
     * @param string $description Event description
     * @param string|Carbon $start Start date
     * @param string|Carbon $end End date
     * @return Event
     */
    private function createEvent($uid, $summary, $description, $start, $end): Event
    {
        // Convert dates to Carbon if they're not already
        if (!($start instanceof Carbon)) {
            $start = Carbon::parse($start);
        }

        if (!($end instanceof Carbon)) {
            $end = Carbon::parse($end);
        }

        // Create event
        $event = new Event(
            new UniqueIdentifier($uid)
        );

        // Set summary and description
        $event->setSummary($summary);
        $event->setDescription($description);

        // Set time span (all day event)
        $event->setOccurrence(
            new TimeSpan(
                new DateTime($start, false),
                new DateTime($end, false)
            )
        );

        return $event;
    }
    
    /**
     * Manages multiple iCal feeds for a property.
     * Allows adding, updating, and removing external calendar sources.
     *
     * @param Property $property The property to manage feeds for
     * @param array $feeds Array of feed objects with url and name properties
     * @return array Results of sync operations
     */
    public function manageFeeds(Property $property, array $feeds): array
    {
        $results = [
            'success' => true,
            'synced' => 0,
            'failed' => 0,
            'details' => []
        ];

        // Get existing feeds from property
        $existingFeeds = json_decode($property->ical_feeds ?? '[]', true);
        $existingSources = collect($existingFeeds)->pluck('name')->toArray();
        
        // Process new/updated feeds
        $updatedFeeds = [];
        $processedSources = [];
        
        foreach ($feeds as $feed) {
            if (empty($feed['url']) || !filter_var($feed['url'], FILTER_VALIDATE_URL)) {
                $results['details'][] = [
                    'name' => $feed['name'] ?? 'Unknown',
                    'success' => false,
                    'message' => 'Invalid URL provided'
                ];
                $results['failed']++;
                continue;
            }
            
            // Generate unique source name if not provided
            $sourceName = $feed['name'] ?? 'feed_' . (count($updatedFeeds) + 1);
            $processedSources[] = $sourceName;
            
            // Sync this feed
            $success = $this->import($property, $feed['url'], $sourceName);
            
            if ($success) {
                $updatedFeeds[] = [
                    'url' => $feed['url'],
                    'name' => $sourceName,
                    'last_sync' => now()->toIso8601String()
                ];
                
                $results['details'][] = [
                    'name' => $sourceName,
                    'success' => true,
                    'message' => 'Calendar synced successfully'
                ];
                
                $results['synced']++;
            } else {
                $results['details'][] = [
                    'name' => $sourceName,
                    'success' => false,
                    'message' => 'Failed to sync calendar'
                ];
                $results['failed']++;
            }
        }
        
        // Check for removed feeds and clear their data
        $removedSources = array_diff($existingSources, $processedSources);
        foreach ($removedSources as $source) {
            PropertyCalendar::where('property_id', $property->id)
                ->where('source', $source)
                ->delete();
            
            $results['details'][] = [
                'name' => $source,
                'success' => true,
                'message' => 'Calendar source removed'
            ];
        }
        
        // Update property with new feeds configuration
        $property->ical_feeds = json_encode($updatedFeeds);
        $property->last_calendar_sync = now();
        $property->save();
        
        $results['success'] = $results['failed'] === 0;
        return $results;
    }
    
    /**
     * Synchronize all iCal feeds for a property
     *
     * @param Property $property
     * @return array Results of the sync operations
     */
    public function syncAllFeeds(Property $property): array
    {
        $results = [
            'success' => true,
            'synced' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // Get existing feeds from property
        $feeds = json_decode($property->ical_feeds ?? '[]', true);
        
        if (empty($feeds)) {
            return [
                'success' => true,
                'message' => 'No iCal feeds configured for this property',
                'synced' => 0,
                'failed' => 0,
                'details' => []
            ];
        }
        
        // Process all feeds
        $updatedFeeds = [];
        
        foreach ($feeds as $feed) {
            if (empty($feed['url']) || !filter_var($feed['url'], FILTER_VALIDATE_URL)) {
                $results['details'][] = [
                    'name' => $feed['name'] ?? 'Unknown',
                    'success' => false,
                    'message' => 'Invalid URL provided'
                ];
                $results['failed']++;
                continue;
            }
            
            // Sync this feed
            $success = $this->import(
                $property, 
                $feed['url'], 
                $feed['name'] ?? 'feed_' . (count($updatedFeeds) + 1)
            );
            
            if ($success) {
                $feed['last_sync'] = now()->toIso8601String();
                $updatedFeeds[] = $feed;
                
                $results['details'][] = [
                    'name' => $feed['name'] ?? 'Unknown',
                    'success' => true,
                    'message' => 'Calendar synced successfully'
                ];
                
                $results['synced']++;
            } else {
                $updatedFeeds[] = $feed;
                
                $results['details'][] = [
                    'name' => $feed['name'] ?? 'Unknown',
                    'success' => false,
                    'message' => 'Failed to sync calendar'
                ];
                $results['failed']++;
            }
        }
        
        // Update property with updated feeds info
        $property->ical_feeds = json_encode($updatedFeeds);
        $property->last_calendar_sync = now();
        $property->save();
        
        $results['success'] = $results['failed'] === 0;
        return $results;
    }
}