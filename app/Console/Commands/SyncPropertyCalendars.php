<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Services\iCalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPropertyCalendars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendars:sync 
                            {--property= : Sync specific property ID}
                            {--force : Force sync even if recently synced}
                            {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync property calendars from external iCal feeds';

    protected iCalService $iCalService;

    /**
     * Create a new command instance.
     */
    public function __construct(iCalService $iCalService)
    {
        parent::__construct();
        $this->iCalService = $iCalService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting calendar synchronization...');

        $propertyId = $this->option('property');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get properties to sync
        $query = Property::whereNotNull('ical_url')
                         ->where('ical_url', '!=', '');

        if ($propertyId) {
            $query->where('id', $propertyId);
        }

        if (!$force) {
            // Only sync properties that haven't been synced in the last hour
            $query->where(function ($q) {
                $q->whereNull('ical_last_sync')
                  ->orWhere('ical_last_sync', '<', now()->subHour());
            });
        }

        $properties = $query->get();

        if ($properties->isEmpty()) {
            $this->info('No properties found for synchronization.');
            return self::SUCCESS;
        }

        $this->info("Found {$properties->count()} properties to sync");

        $successCount = 0;
        $errorCount = 0;

        foreach ($properties as $property) {
            $this->line("Syncing property: {$property->title} (ID: {$property->id})");
            
            if ($dryRun) {
                $this->info("  Would sync from: {$property->ical_url}");
                continue;
            }

            try {
                $success = $this->iCalService->import($property, $property->ical_url);
                
                if ($success) {
                    $property->update([
                        'ical_last_sync' => now(),
                        'ical_sync_status' => 'success'
                    ]);
                    
                    $this->info("  ✓ Successfully synced");
                    $successCount++;
                } else {
                    $property->update([
                        'ical_sync_status' => 'failed',
                        'ical_last_error' => 'Sync failed - check logs for details'
                    ]);
                    
                    $this->error("  ✗ Sync failed");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $property->update([
                    'ical_sync_status' => 'failed',
                    'ical_last_error' => $e->getMessage()
                ]);
                
                $this->error("  ✗ Error: {$e->getMessage()}");
                Log::error("Calendar sync failed for property {$property->id}", [
                    'property_id' => $property->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Synchronization complete!");
        $this->info("Successful: {$successCount}");
        
        if ($errorCount > 0) {
            $this->error("Failed: {$errorCount}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
