<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ContentBlock;
use Illuminate\View\View;

class ContentController extends Controller
{
    /**
     * Get featured cities
     */
    public function getFeaturedCities(): JsonResponse
    {
        $cities = app(\App\Services\SettingsService::class)->get('featured_cities', []);
        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function index(): View
    {
        return view('admin.content.index');
    }

    public function pages(): View
    {
        return view('admin.content.pages');
    }

    public function sliders(): View
    {
        return view('admin.content.sliders');
    }

    /**
     * Update featured cities
     */
    public function updateFeaturedCities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cities' => 'required|array'
        ]);

        app(\App\Services\SettingsService::class)->set('featured_cities', json_encode($data['cities']), 'json');

        return response()->json(['success' => true]);
    }

    /**
     * Get home page sliders
     */
    public function getSliders(): JsonResponse
    {
        $sliders = app(\App\Services\SettingsService::class)->get('home_sliders', []);
        return response()->json([
            'success' => true,
            'data' => $sliders
        ]);
    }

    /**
     * Update home page sliders
     */
    public function updateSliders(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sliders' => 'required|array'
        ]);

        app(\App\Services\SettingsService::class)->set('home_sliders', json_encode($data['sliders']), 'json');

        return response()->json(['success' => true]);
    }
}
