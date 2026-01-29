<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('host.reports.financial');
    }
}