<?php

namespace App\Traits;

trait HasProfileCompletion
{
    /**
     * Calculate profile completion percentage
     */
    public function calculateProfileCompletion(): int
    {
        $requiredFields = [
            'name',
            'email', 
            'phone',
            'bio',
            'avatar'
        ];
        
        $completedFields = 0;
        $totalFields = count($requiredFields);
        
        foreach ($requiredFields as $field) {
            if (!empty($this->{$field})) {
                $completedFields++;
            }
        }
        
        // Add verification bonuses
        if ($this->email_verified_at) {
            $completedFields++;
            $totalFields++;
        }
        
        if ($this->identity_verified) {
            $completedFields++;
            $totalFields++;
        }
        
        return round(($completedFields / $totalFields) * 100);
    }

    /**
     * Get missing profile fields
     */
    public function getMissingProfileFields(): array
    {
        $requiredFields = [
            'name' => 'Full Name',
            'phone' => 'Phone Number',
            'bio' => 'Bio/Description',
            'avatar' => 'Profile Photo'
        ];
        
        $missingFields = [];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($this->{$field})) {
                $missingFields[] = [
                    'field' => $field,
                    'label' => $label,
                    'required' => true
                ];
            }
        }
        
        // Add verification fields
        if (!$this->email_verified_at) {
            $missingFields[] = [
                'field' => 'email_verification',
                'label' => 'Email Verification',
                'required' => true
            ];
        }
        
        if (!$this->identity_verified) {
            $missingFields[] = [
                'field' => 'identity_verification',
                'label' => 'Identity Verification',
                'required' => false
            ];
        }
        
        return $missingFields;
    }

    /**
     * Check if profile is complete enough for hosting
     */
    public function canHost(): bool
    {
        return $this->calculateProfileCompletion() >= 80 && 
               $this->email_verified_at && 
               $this->identity_verified;
    }

    /**
     * Check if profile is complete enough for booking
     */
    public function canBook(): bool
    {
        return $this->calculateProfileCompletion() >= 60 && 
               $this->email_verified_at;
    }
}