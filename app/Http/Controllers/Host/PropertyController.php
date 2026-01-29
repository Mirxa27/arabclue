<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('host.properties.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('host.properties.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        return view('host.properties.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        return view('host.properties.edit');
    }
}