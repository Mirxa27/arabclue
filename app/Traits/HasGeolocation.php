<?php

namespace App\Traits;

trait HasGeolocation
{
    /**
     * Calculate distance between two points in kilometers
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // Earth's radius in kilometers
        
        $latDiff = deg2rad($latitude - $this->latitude);
        $lonDiff = deg2rad($longitude - $this->longitude);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Find nearby properties within radius
     */
    public static function nearby(float $latitude, float $longitude, float $radiusKm = 10)
    {
        return static::selectRaw(
            "*, (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance",
            [$latitude, $longitude, $latitude]
        )
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinates(): ?array
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude
        ];
    }

    /**
     * Update coordinates
     */
    public function updateCoordinates(float $latitude, float $longitude): void
    {
        $this->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'coordinates_updated_at' => now()
        ]);
    }

    /**
     * Geocode address to coordinates
     */
    public function geocodeAddress(string $address = null): bool
    {
        $address = $address ?? $this->getFullAddress();
        
        if (!$address) {
            return false;
        }

        // This would integrate with a geocoding service like Google Maps API
        // For now, returning false to indicate no geocoding service configured
        return false;
    }

    /**
     * Get full address string
     */
    protected function getFullAddress(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Check if coordinates are valid
     */
    public function hasValidCoordinates(): bool
    {
        return !empty($this->latitude) && 
               !empty($this->longitude) &&
               $this->latitude >= -90 && $this->latitude <= 90 &&
               $this->longitude >= -180 && $this->longitude <= 180;
    }

    /**
     * Get map URL
     */
    public function getMapUrl(string $provider = 'google'): ?string
    {
        if (!$this->hasValidCoordinates()) {
            return null;
        }

        return match($provider) {
            'google' => "https://maps.google.com/maps?q={$this->latitude},{$this->longitude}",
            'apple' => "https://maps.apple.com/?q={$this->latitude},{$this->longitude}",
            'openstreetmap' => "https://www.openstreetmap.org/?mlat={$this->latitude}&mlon={$this->longitude}&zoom=15",
            default => null
        };
    }

    /**
     * Scope to filter by location bounds
     */
    public function scopeWithinBounds($query, array $bounds)
    {
        return $query->whereBetween('latitude', [$bounds['south'], $bounds['north']])
                    ->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
    }

    /**
     * Scope to filter by radius
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, float $radiusKm)
    {
        return $query->selectRaw(
            "*, (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance",
            [$latitude, $longitude, $latitude]
        )
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }
}