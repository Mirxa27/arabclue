<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Base Controller - Foundation for all application controllers
 * 
 * Implements common functionality, response formatting, and
 * utility methods for consistent API and web responses
 * 
 * @package App\Http\Controllers
 * @version 1.0.0
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * Success response for API
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    /**
     * Error response for API
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $errors Validation errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $code);
    }
    
    /**
     * Paginated response for API
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem()
            ]
        ]);
    }
    
    /**
     * Get authenticated user with caching
     * 
     * @return \App\Models\User|null
     */
    protected function getAuthUser()
    {
        if (!auth()->check()) {
            return null;
        }
        
        return cache()->remember(
            'user_' . auth()->id(),
            now()->addMinutes(5),
            fn() => auth()->user()->load(['preferences', 'adminPreferences'])
        );
    }
    
    /**
     * Get request platform (web, mobile, api)
     * 
     * @return string
     */
    protected function getPlatform(): string
    {
        $userAgent = request()->header('User-Agent', '');
        
        if (request()->is('api/*')) {
            return 'api';
        }
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $userAgent)) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return 'mobile';
        }
        
        return 'web';
    }
    
    /**
     * Get user's preferred language
     * 
     * @return string
     */
    protected function getUserLanguage(): string
    {
        if ($user = $this->getAuthUser()) {
            return $user->language;
        }
        
        return session('locale', config('app.locale'));
    }
    
    /**
     * Log user activity
     * 
     * @param string $action
     * @param array $data
     */
    protected function logActivity(string $action, array $data = []): void
    {
        if ($user = $this->getAuthUser()) {
            activity()
                ->causedBy($user)
                ->withProperties($data)
                ->log($action);
        }
    }
}
