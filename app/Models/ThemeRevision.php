<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * ThemeRevision Model - Theme Version Control Management
 * 
 * Manages theme revisions for version control, rollback capabilities,
 * and change tracking with comprehensive metadata storage
 * 
 * @property int $id
 * @property int $theme_id
 * @property int $revision_number
 * @property int $created_by
 * @property array $theme_data
 * @property array $changes
 * @property string $description
 * @property string $version_tag
 * @property bool $is_major_version
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ThemeRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'revision_number',
        'created_by',
        'theme_data',
        'changes',
        'description',
        'version_tag',
        'is_major_version',
        'backup_file_path',
        'file_hash',
        'size_bytes'
    ];

    protected $casts = [
        'theme_data' => 'array',
        'changes' => 'array',
        'is_major_version' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relationship: Revision belongs to a theme
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Relationship: Revision created by user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get major versions only
     */
    public function scopeMajorVersions($query)
    {
        return $query->where('is_major_version', true);
    }

    /**
     * Scope to get by theme
     */
    public function scopeByTheme($query, int $themeId)
    {
        return $query->where('theme_id', $themeId);
    }

    /**
     * Scope to get latest revisions first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('revision_number', 'desc');
    }

    /**
     * Create new revision from theme
     */
    public static function createFromTheme(Theme $theme, string $description = null, bool $isMajor = false): self
    {
        $lastRevision = static::byTheme($theme->id)
            ->latest()
            ->first();
        
        $revisionNumber = $lastRevision ? $lastRevision->revision_number + 1 : 1;
        
        // Capture current theme state
        $themeData = [
            'name' => $theme->name,
            'slug' => $theme->slug,
            'description' => $theme->description,
            'color_scheme' => $theme->color_scheme,
            'typography_settings' => $theme->typography_settings,
            'layout_settings' => $theme->layout_settings,
            'component_styles' => $theme->component_styles,
            'custom_css' => $theme->custom_css,
            'custom_js' => $theme->custom_js,
            'is_active' => $theme->is_active,
            'is_default' => $theme->is_default,
            'configuration' => $theme->configuration
        ];
        
        // Calculate changes from previous revision
        $changes = [];
        if ($lastRevision) {
            $changes = static::calculateChanges($lastRevision->theme_data, $themeData);
        }
        
        // Generate version tag
        $versionTag = static::generateVersionTag($theme, $revisionNumber, $isMajor);
        
        // Create backup file
        $backupPath = static::createBackupFile($theme, $revisionNumber);
        
        return static::create([
            'theme_id' => $theme->id,
            'revision_number' => $revisionNumber,
            'created_by' => Auth::id(),
            'theme_data' => $themeData,
            'changes' => $changes,
            'description' => $description ?? "Revision {$revisionNumber}",
            'version_tag' => $versionTag,
            'is_major_version' => $isMajor,
            'backup_file_path' => $backupPath,
            'file_hash' => static::calculateFileHash($themeData),
            'size_bytes' => strlen(json_encode($themeData))
        ]);
    }

    /**
     * Calculate changes between theme data
     */
    protected static function calculateChanges(array $oldData, array $newData): array
    {
        $changes = [];
        
        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key])) {
                $changes['added'][$key] = $value;
            } elseif ($oldData[$key] !== $value) {
                $changes['modified'][$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }
        
        foreach ($oldData as $key => $value) {
            if (!isset($newData[$key])) {
                $changes['removed'][$key] = $value;
            }
        }
        
        return $changes;
    }

    /**
     * Generate version tag
     */
    protected static function generateVersionTag(Theme $theme, int $revisionNumber, bool $isMajor): string
    {
        if ($isMajor) {
            $majorCount = static::byTheme($theme->id)->majorVersions()->count() + 1;
            return "v{$majorCount}.0";
        }
        
        $lastMajor = static::byTheme($theme->id)->majorVersions()->latest()->first();
        $majorVersion = $lastMajor ? (int) explode('.', $lastMajor->version_tag)[0] : 1;
        $minorCount = static::byTheme($theme->id)
            ->where('revision_number', '>', $lastMajor?->revision_number ?? 0)
            ->count();
        
        return "v{$majorVersion}.{$minorCount}";
    }

    /**
     * Create backup file
     */
    protected static function createBackupFile(Theme $theme, int $revisionNumber): string
    {
        $backupDir = storage_path('app/theme-backups/' . $theme->id);
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $filename = "revision_{$revisionNumber}_" . date('Y-m-d_H-i-s') . '.json';
        $filepath = $backupDir . '/' . $filename;
        
        $backupData = [
            'theme_id' => $theme->id,
            'revision_number' => $revisionNumber,
            'created_at' => now()->toISOString(),
            'theme_data' => $theme->toArray(),
            'layouts' => $theme->pageLayouts->toArray(),
            'components' => $theme->pageLayouts->flatMap->uiComponents->toArray()
        ];
        
        file_put_contents($filepath, json_encode($backupData, JSON_PRETTY_PRINT));
        
        return "theme-backups/{$theme->id}/{$filename}";
    }

    /**
     * Calculate file hash
     */
    protected static function calculateFileHash(array $data): string
    {
        return hash('sha256', json_encode($data));
    }

    /**
     * Restore theme to this revision
     */
    public function restoreTheme(): bool
    {
        try {
            $themeData = $this->theme_data;
            
            $this->theme->update([
                'name' => $themeData['name'],
                'description' => $themeData['description'],
                'color_scheme' => $themeData['color_scheme'],
                'typography_settings' => $themeData['typography_settings'],
                'layout_settings' => $themeData['layout_settings'],
                'component_styles' => $themeData['component_styles'],
                'custom_css' => $themeData['custom_css'],
                'custom_js' => $themeData['custom_js'],
                'configuration' => $themeData['configuration']
            ]);
            
            // Create new revision for the restore action
            static::createFromTheme(
                $this->theme,
                "Restored to revision {$this->revision_number} ({$this->version_tag})"
            );
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to restore theme revision', [
                'revision_id' => $this->id,
                'theme_id' => $this->theme_id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get revision summary
     */
    public function getSummary(): array
    {
        $changes = $this->changes ?? [];
        
        return [
            'revision_number' => $this->revision_number,
            'version_tag' => $this->version_tag,
            'description' => $this->description,
            'created_at' => $this->created_at->toISOString(),
            'created_by' => $this->creator?->name,
            'is_major_version' => $this->is_major_version,
            'changes_count' => [
                'added' => count($changes['added'] ?? []),
                'modified' => count($changes['modified'] ?? []),
                'removed' => count($changes['removed'] ?? [])
            ],
            'size' => $this->formatFileSize($this->size_bytes),
            'has_backup' => !empty($this->backup_file_path)
        ];
    }

    /**
     * Get detailed changes
     */
    public function getDetailedChanges(): array
    {
        $changes = $this->changes ?? [];
        $formatted = [];
        
        foreach ($changes['added'] ?? [] as $key => $value) {
            $formatted[] = [
                'type' => 'added',
                'field' => $key,
                'description' => "Added {$key}",
                'new_value' => $value
            ];
        }
        
        foreach ($changes['modified'] ?? [] as $key => $change) {
            $formatted[] = [
                'type' => 'modified',
                'field' => $key,
                'description' => "Modified {$key}",
                'old_value' => $change['old'],
                'new_value' => $change['new']
            ];
        }
        
        foreach ($changes['removed'] ?? [] as $key => $value) {
            $formatted[] = [
                'type' => 'removed',
                'field' => $key,
                'description' => "Removed {$key}",
                'old_value' => $value
            ];
        }
        
        return $formatted;
    }

    /**
     * Compare with another revision
     */
    public function compareWith(self $otherRevision): array
    {
        return static::calculateChanges(
            $otherRevision->theme_data,
            $this->theme_data
        );
    }

    /**
     * Get backup file path
     */
    public function getBackupFilePath(): ?string
    {
        if (!$this->backup_file_path) {
            return null;
        }
        
        return storage_path('app/' . $this->backup_file_path);
    }

    /**
     * Download backup file
     */
    public function downloadBackup(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filePath = $this->getBackupFilePath();
        
        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'Backup file not found');
        }
        
        $filename = "theme-{$this->theme->slug}-revision-{$this->revision_number}.json";
        
        return response()->streamDownload(function () use ($filePath) {
            echo file_get_contents($filePath);
        }, $filename, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Format file size
     */
    protected function formatFileSize(?int $bytes): string
    {
        if (!$bytes) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes) / log(1024));
        
        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Delete old revisions beyond limit
     */
    public static function cleanupOldRevisions(int $themeId, int $keepCount = 50): int
    {
        $revisions = static::byTheme($themeId)
            ->latest()
            ->skip($keepCount)
            ->pluck('id');
        
        $deletedCount = 0;
        
        foreach ($revisions as $revisionId) {
            $revision = static::find($revisionId);
            if ($revision) {
                // Delete backup file
                $backupPath = $revision->getBackupFilePath();
                if ($backupPath && file_exists($backupPath)) {
                    unlink($backupPath);
                }
                
                $revision->delete();
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        return "{$this->version_tag} - {$this->description}";
    }
}