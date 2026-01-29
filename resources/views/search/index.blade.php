@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <h1 class="text-3xl font-bold mb-4">Search Results</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Property cards will be rendered here --}}
            </div>
        </div>
        <div class="hidden lg:block">
            <div class="sticky top-24">
                <map-view :properties="properties"></map-view>
            </div>
        </div>
    </div>
</div>
@endsection
