<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Referral;

class ReferralController extends Controller
{
    /**
     * Get referral settings
     */
    public function getSettings(): JsonResponse
    {
        $settings = app(\App\Services\SettingsService::class)->get('referral_settings', []);
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update referral settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reward_amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'enabled' => 'required|boolean'
        ]);

        app(\App\Services\SettingsService::class)->set('referral_settings', json_encode($data), 'json');

        return response()->json(['success' => true]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $referrals = Referral::with(['referrer', 'referred'])->paginate();
        return response()->json(['success' => true, 'data' => $referrals]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Referral $referral): JsonResponse
    {
        $referral->load(['referrer', 'referred']);
        return response()->json(['success' => true, 'data' => $referral]);
    }
}
