<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the main reports page.
     */
    public function index(): View
    {
        return view('admin.reports.index');
    }

    /**
     * Display the revenue report page.
     */
    public function revenue(): View
    {
        return view('admin.reports.revenue');
    }

    /**
     * Display the bookings report page.
     */
    public function bookings(): View
    {
        return view('admin.reports.bookings');
    }
}