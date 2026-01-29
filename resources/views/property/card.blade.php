<div class="property-card">
    <a href="{{ route('property.show', ['slug' => $property->slug]) }}">
        <img src="{{ $property->featured_image }}" alt="{{ $property->title }}" class="card-img-top">
    </a>
    <div class="card-body">
        <h5 class="card-title">{{ $property->title }}</h5>
        <p class="card-text">
            {{ Str::limit($property->description, 100) }}
        </p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="price">${{ $property->price_per_night }}/night</span>
            @auth
                <button class="btn btn-primary wishlist-toggle" data-property-id="{{ $property->id }}">
                    @if (auth()->user()->wishlists->contains('property_id', $property->id))
                        <i class="fas fa-heart"></i>
                    @else
                        <i class="far fa-heart"></i>
                    @endif
                </button>
            @endauth
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.wishlist-toggle').click(function() {
            let propertyId = $(this).data('property-id');
            let button = $(this);

            $.ajax({
                url: '/api/v1/wishlist/' + propertyId,
                type: button.find('.fas').length ? 'DELETE' : 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (button.find('.fas').length) {
                        button.html('<i class="far fa-heart"></i>');
                    } else {
                        button.html('<i class="fas fa-heart"></i>');
                    }
                    console.log(response.message);
                },
                error: function(error) {
                    console.error('Error toggling wishlist:', error);
                }
            });
        });
    });
</script>
