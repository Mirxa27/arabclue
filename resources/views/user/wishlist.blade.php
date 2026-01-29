@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Wishlist</h1>

    @if ($wishlists->isEmpty())
        <p>Your wishlist is empty.</p>
    @else
        <div class="row">
            @foreach ($wishlists as $wishlist)
                <div class="col-md-4 mb-4">
                    @include('property.card', ['property' => $wishlist->property])
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
