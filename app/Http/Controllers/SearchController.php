<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PropertySearchService;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(PropertySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        $properties = $this->searchService->search($request->all());
        return view('search.index', compact('properties'));
    }
}
