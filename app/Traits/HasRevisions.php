<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasRevisions
{
    /**
     * Boot the trait
     */
    protected static function bootHasRevisions(): void
    {
        static::updating(function ($model) {
            $model->createRevision();
        });
    }

    /**
     * Create a revision of the current model state
     */
    public function createRevision(): void
    {
        $changes = $this->getDirty();
        
        if (empty($changes)) {
            return;
        }

        $revisionData = [
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'user_id' => Auth::id(),
            'changes' => $changes,
            'original_data' => $this->getOriginal(),
            'revision_number' => $this->getNextRevisionNumber(),
            'created_at' => now()
        ];

        $this->storeRevision($revisionData);
    }

    /**
     * Get all revisions for this model
     */
    public function revisions()
    {
        // This would typically relate to a separate revisions table
        // For now, we'll store revisions in the model's revision_history JSON field
        return collect($this->revision_history ?? []);
    }

    /**
     * Get the latest revision
     */
    public function latestRevision(): ?array
    {
        $revisions = $this->revisions();
        return $revisions->isNotEmpty() ? $revisions->last() : null;
    }

    /**
     * Get a specific revision by number
     */
    public function getRevision(int $revisionNumber): ?array
    {
        return $this->revisions()
            ->where('revision_number', $revisionNumber)
            ->first();
    }

    /**
     * Restore to a specific revision
     */
    public function restoreToRevision(int $revisionNumber): bool
    {
        $revision = $this->getRevision($revisionNumber);
        
        if (!$revision) {
            return false;
        }

        // Restore the original data from the revision
        $originalData = $revision['original_data'];
        
        foreach ($originalData as $key => $value) {
            if ($this->isFillable($key)) {
                $this->{$key} = $value;
            }
        }

        $this->save();
        return true;
    }

    /**
     * Compare two revisions
     */
    public function compareRevisions(int $fromRevision, int $toRevision): array
    {
        $from = $this->getRevision($fromRevision);
        $to = $this->getRevision($toRevision);
        
        if (!$from || !$to) {
            return [];
        }

        $fromData = $from['original_data'];
        $toData = $to['original_data'];
        
        $differences = [];
        
        foreach ($toData as $key => $value) {
            if (isset($fromData[$key]) && $fromData[$key] !== $value) {
                $differences[$key] = [
                    'from' => $fromData[$key],
                    'to' => $value
                ];
            }
        }
        
        return $differences;
    }

    /**
     * Get revision history with metadata
     */
    public function getRevisionHistory(): array
    {
        return $this->revisions()
            ->map(function ($revision) {
                return [
                    'revision_number' => $revision['revision_number'],
                    'created_at' => $revision['created_at'],
                    'user_id' => $revision['user_id'],
                    'changes_count' => count($revision['changes']),
                    'summary' => $this->generateRevisionSummary($revision['changes'])
                ];
            })
            ->toArray();
    }

    /**
     * Generate a human-readable summary of changes
     */
    protected function generateRevisionSummary(array $changes): string
    {
        $summaryParts = [];
        
        foreach ($changes as $field => $value) {
            $fieldName = $this->getHumanReadableFieldName($field);
            $summaryParts[] = "Updated {$fieldName}";
        }
        
        return implode(', ', $summaryParts);
    }

    /**
     * Get human-readable field name
     */
    protected function getHumanReadableFieldName(string $field): string
    {
        $fieldMappings = [
            'title' => 'Title',
            'description' => 'Description',
            'price_per_night' => 'Price per Night',
            'max_guests' => 'Maximum Guests',
            'status' => 'Status',
            'is_active' => 'Active Status'
        ];
        
        return $fieldMappings[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Store revision data
     */
    protected function storeRevision(array $revisionData): void
    {
        $currentHistory = $this->revision_history ?? [];
        $currentHistory[] = $revisionData;
        
        // Keep only the last 50 revisions to prevent bloat
        if (count($currentHistory) > 50) {
            $currentHistory = array_slice($currentHistory, -50);
        }
        
        $this->updateQuietly(['revision_history' => $currentHistory]);
    }

    /**
     * Get the next revision number
     */
    protected function getNextRevisionNumber(): int
    {
        $lastRevision = $this->latestRevision();
        return $lastRevision ? $lastRevision['revision_number'] + 1 : 1;
    }

    /**
     * Check if model has revisions
     */
    public function hasRevisions(): bool
    {
        return $this->revisions()->isNotEmpty();
    }

    /**
     * Get revision count
     */
    public function getRevisionCount(): int
    {
        return $this->revisions()->count();
    }

    /**
     * Delete old revisions beyond a certain limit
     */
    public function cleanupOldRevisions(int $keepCount = 20): void
    {
        $revisions = $this->revisions();
        
        if ($revisions->count() > $keepCount) {
            $revisionsToKeep = $revisions->sortByDesc('revision_number')
                ->take($keepCount)
                ->values()
                ->toArray();
                
            $this->updateQuietly(['revision_history' => $revisionsToKeep]);
        }
    }

    /**
     * Get revisions by date range
     */
    public function getRevisionsByDateRange(string $startDate, string $endDate): array
    {
        return $this->revisions()
            ->filter(function ($revision) use ($startDate, $endDate) {
                $revisionDate = $revision['created_at'];
                return $revisionDate >= $startDate && $revisionDate <= $endDate;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get revisions by user
     */
    public function getRevisionsByUser(int $userId): array
    {
        return $this->revisions()
            ->where('user_id', $userId)
            ->values()
            ->toArray();
    }

    /**
     * Export revisions to array
     */
    public function exportRevisions(): array
    {
        return [
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'current_state' => $this->toArray(),
            'revisions' => $this->getRevisionHistory(),
            'exported_at' => now()->toISOString()
        ];
    }

    /**
     * Check if a field should be tracked for revisions
     */
    protected function shouldTrackField(string $field): bool
    {
        $excludedFields = [
            'updated_at',
            'created_at',
            'deleted_at',
            'revision_history',
            'id'
        ];
        
        return !in_array($field, $excludedFields);
    }

    /**
     * Get fields that have changed in the latest revision
     */
    public function getChangedFields(): array
    {
        $latestRevision = $this->latestRevision();
        
        if (!$latestRevision) {
            return [];
        }
        
        return array_keys($latestRevision['changes']);
    }

    /**
     * Check if a specific field was changed in the latest revision
     */
    public function wasFieldChanged(string $field): bool
    {
        return in_array($field, $this->getChangedFields());
    }

    /**
     * Get the previous value of a field from the latest revision
     */
    public function getPreviousValue(string $field)
    {
        $latestRevision = $this->latestRevision();
        
        if (!$latestRevision || !isset($latestRevision['original_data'][$field])) {
            return null;
        }
        
        return $latestRevision['original_data'][$field];
    }
}