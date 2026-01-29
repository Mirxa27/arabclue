<?php

namespace App\Traits;

trait HasMobileAppSupport
{
    /**
     * Update device information for mobile app
     */
    public function updateDeviceInfo(array $deviceInfo): void
    {
        $existingInfo = $this->device_info ?? [];
        
        $this->update([
            'device_info' => array_merge($existingInfo, [
                'device_type' => $deviceInfo['device_type'] ?? 'unknown',
                'os' => $deviceInfo['os'] ?? 'unknown',
                'os_version' => $deviceInfo['os_version'] ?? 'unknown',
                'app_version' => $deviceInfo['app_version'] ?? 'unknown',
                'push_enabled' => $deviceInfo['push_enabled'] ?? false,
                'last_active' => now()->toISOString(),
                'timezone' => $deviceInfo['timezone'] ?? 'UTC',
                'language' => $deviceInfo['language'] ?? 'en',
                'screen_resolution' => $deviceInfo['screen_resolution'] ?? null
            ]),
            'fcm_token' => $deviceInfo['fcm_token'] ?? $this->fcm_token,
            'apns_token' => $deviceInfo['apns_token'] ?? $this->apns_token
        ]);
    }

    /**
     * Send push notification to user
     */
    public function sendPushNotification(string $title, string $body, array $data = []): bool
    {
        if (!$this->hasPushTokens()) {
            return false;
        }

        try {
            $notification = [
                'title' => $title,
                'body' => $body,
                'data' => array_merge($data, [
                    'timestamp' => now()->toISOString(),
                    'user_id' => $this->id
                ])
            ];

            // Send to FCM (Android)
            if ($this->fcm_token) {
                $this->sendFCMNotification($notification);
            }

            // Send to APNS (iOS)
            if ($this->apns_token) {
                $this->sendAPNSNotification($notification);
            }

            return true;
        } catch (\Exception $e) {
            logger()->error('Push notification failed', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user has push notification tokens
     */
    public function hasPushTokens(): bool
    {
        return !empty($this->fcm_token) || !empty($this->apns_token);
    }

    /**
     * Check if user has enabled push notifications
     */
    public function hasPushNotificationsEnabled(): bool
    {
        $deviceInfo = $this->device_info ?? [];
        return ($deviceInfo['push_enabled'] ?? false) && $this->hasPushTokens();
    }

    /**
     * Get mobile app preferences
     */
    public function getMobileAppPreferences(): array
    {
        $deviceInfo = $this->device_info ?? [];
        $notificationSettings = $this->notification_settings ?? [];
        
        return [
            'push_notifications' => $notificationSettings['push'] ?? [],
            'app_theme' => $deviceInfo['theme'] ?? 'auto',
            'offline_mode' => $deviceInfo['offline_mode'] ?? true,
            'auto_sync' => $deviceInfo['auto_sync'] ?? true,
            'background_refresh' => $deviceInfo['background_refresh'] ?? true,
            'biometric_auth' => $deviceInfo['biometric_auth'] ?? false
        ];
    }

    /**
     * Update mobile app preferences
     */
    public function updateMobileAppPreferences(array $preferences): void
    {
        $deviceInfo = $this->device_info ?? [];
        $notificationSettings = $this->notification_settings ?? [];

        // Update device-specific preferences
        if (isset($preferences['app_theme'])) {
            $deviceInfo['theme'] = $preferences['app_theme'];
        }

        if (isset($preferences['offline_mode'])) {
            $deviceInfo['offline_mode'] = $preferences['offline_mode'];
        }

        if (isset($preferences['auto_sync'])) {
            $deviceInfo['auto_sync'] = $preferences['auto_sync'];
        }

        if (isset($preferences['background_refresh'])) {
            $deviceInfo['background_refresh'] = $preferences['background_refresh'];
        }

        if (isset($preferences['biometric_auth'])) {
            $deviceInfo['biometric_auth'] = $preferences['biometric_auth'];
        }

        // Update push notification preferences
        if (isset($preferences['push_notifications'])) {
            $notificationSettings['push'] = array_merge(
                $notificationSettings['push'] ?? [],
                $preferences['push_notifications']
            );
        }

        $this->update([
            'device_info' => $deviceInfo,
            'notification_settings' => $notificationSettings
        ]);
    }

    /**
     * Register app installation
     */
    public function registerAppInstallation(array $installationData): void
    {
        $deviceInfo = $this->device_info ?? [];
        
        $deviceInfo['installation'] = [
            'installed_at' => now()->toISOString(),
            'install_source' => $installationData['source'] ?? 'unknown',
            'referrer' => $installationData['referrer'] ?? null,
            'campaign' => $installationData['campaign'] ?? null
        ];

        $this->update(['device_info' => $deviceInfo]);
    }

    /**
     * Track app usage
     */
    public function trackAppUsage(array $usageData): void
    {
        $deviceInfo = $this->device_info ?? [];
        
        $deviceInfo['usage'] = [
            'last_active' => now()->toISOString(),
            'session_duration' => $usageData['session_duration'] ?? null,
            'screen_time' => $usageData['screen_time'] ?? null,
            'features_used' => $usageData['features_used'] ?? []
        ];

        $this->update(['device_info' => $deviceInfo]);
    }

    /**
     * Send FCM notification
     */
    protected function sendFCMNotification(array $notification): void
    {
        // Implementation would depend on your FCM service
        // This is a placeholder for the actual FCM integration
        if (app()->bound('fcm.service')) {
            app('fcm.service')->send($this->fcm_token, $notification);
        }
    }

    /**
     * Send APNS notification
     */
    protected function sendAPNSNotification(array $notification): void
    {
        // Implementation would depend on your APNS service
        // This is a placeholder for the actual APNS integration
        if (app()->bound('apns.service')) {
            app('apns.service')->send($this->apns_token, $notification);
        }
    }

    /**
     * Get device type
     */
    public function getDeviceType(): string
    {
        $deviceInfo = $this->device_info ?? [];
        return $deviceInfo['device_type'] ?? 'unknown';
    }

    /**
     * Check if user is on mobile device
     */
    public function isMobileUser(): bool
    {
        $deviceType = $this->getDeviceType();
        return in_array($deviceType, ['ios', 'android', 'mobile']);
    }

    /**
     * Get app version
     */
    public function getAppVersion(): ?string
    {
        $deviceInfo = $this->device_info ?? [];
        return $deviceInfo['app_version'] ?? null;
    }

    /**
     * Check if app needs update
     */
    public function needsAppUpdate(string $minimumVersion): bool
    {
        $currentVersion = $this->getAppVersion();
        
        if (!$currentVersion) {
            return false;
        }

        return version_compare($currentVersion, $minimumVersion, '<');
    }
}