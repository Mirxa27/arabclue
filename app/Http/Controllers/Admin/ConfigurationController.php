<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Admin Configuration Controller
 * 
 * Manages system-wide configuration settings
 * including environment variables exposed to the admin UI
 */
class ConfigurationController extends Controller
{
    protected $settingsService;
    protected $configService;
    
    public function __construct(SettingsService $settingsService, ConfigurationService $configService)
    {
        $this->settingsService = $settingsService;
        $this->configService = $configService;
        $this->middleware('admin');
    }
    
    /**
     * Get all configuration categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = $this->configService->getCategories();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * Get configuration settings by category
     */
    public function getConfigurationByCategory(Request $request, string $category): JsonResponse
    {
        try {
            $configuration = $this->configService->getConfigurationByCategory($category);
            
            return response()->json([
                'success' => true,
                'data' => $configuration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Update configuration settings
     */
    public function updateConfiguration(Request $request): JsonResponse
    {
        // Validate admin permissions
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $category = $request->input('category');
            $settings = $request->input('settings');
            
            $result = $this->configService->updateConfiguration($category, $settings);
            
            // Log configuration changes
            Log::info('Admin configuration updated', [
                'admin_id' => Auth::id(),
                'category' => $category,
                'keys_updated' => collect($settings)->pluck('key')->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Configuration update failed', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'category' => $request->input('category')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get system environment variables that are safe to expose
     */
    public function getExposedEnvironmentVariables(): JsonResponse
    {
        // Validate admin permissions
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $variables = $this->configService->getExposedEnvironmentVariables();
        
        return response()->json([
            'success' => true,
            'data' => $variables
        ]);
    }
    
    /**
     * Update environment variables
     */
    public function updateEnvironmentVariables(Request $request): JsonResponse
    {
        // Validate admin permissions
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'variables' => 'required|array',
            'variables.*.key' => 'required|string',
            'variables.*.value' => 'nullable'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $variables = $request->input('variables');
            
            $result = $this->configService->updateEnvironmentVariables($variables);
            
            // Log environment changes
            Log::info('Admin updated environment variables', [
                'admin_id' => Auth::id(),
                'keys_updated' => collect($variables)->pluck('key')->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Environment variables updated successfully',
                'data' => [
                    'updated' => $result['updated'],
                    'restart_required' => $result['restart_required']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Environment variable update failed', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update environment variables',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
