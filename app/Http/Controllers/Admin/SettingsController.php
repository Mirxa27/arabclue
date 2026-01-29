<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
 
class SettingsController extends Controller
{
    /**
     * Display the main settings page.
     */
    public function index(): View
    {
        return view('admin.settings.index');
    }

    /**
     * Display the payment gateways settings page.
     */
    public function paymentGateways(): View
    {
        return view('admin.settings.payment-gateways');
    }

    /**
     * Display the currencies settings page.
     */
    public function currencies(): View
    {
        return view('admin.settings.currencies');
    }

    /**
     * Display the languages settings page.
     */
    public function languages(): View
    {
        return view('admin.settings.languages');
    }

    /**
     * Get currencies
     */
    public function getCurrencies(): JsonResponse
    {
        $currencies = app(\App\Services\SettingsService::class)->get('supported_currencies', ['SAR']);
        return response()->json([
            'success' => true,
            'data' => $currencies
        ]);
    }

    /**
     * Update currencies
     */
    public function updateCurrencies(Request $request): JsonResponse
    {
        $data = $request->validate([
            'currencies' => 'required|array'
        ]);

        app(\App\Services\SettingsService::class)->set('supported_currencies', json_encode($data['currencies']), 'json');

        return response()->json(['success' => true]);
    }

    /**
     * Get languages
     */
    public function getLanguages(): JsonResponse
    {
        $languages = app(\App\Services\SettingsService::class)->get('supported_languages', ['en']);
        return response()->json([
            'success' => true,
            'data' => $languages
        ]);
    }

    /**
     * Update languages
     */
    public function updateLanguages(Request $request): JsonResponse
    {
        $data = $request->validate([
            'languages' => 'required|array'
        ]);

        app(\App\Services\SettingsService::class)->set('supported_languages', json_encode($data['languages']), 'json');

        return response()->json(['success' => true]);
    }
}
