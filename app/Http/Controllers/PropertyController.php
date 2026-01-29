<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // In a real application, you'd fetch properties from the database
        $properties = Property::where('status', 'active')->paginate(12);
        return view('stays', compact('properties'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\View\View
     */
    public function show(Property $property): View
    {
        return view('properties.show', compact('property'));
    }
}
