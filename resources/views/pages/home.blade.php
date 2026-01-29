@extends('layouts.app')

@section('title', 'HabibiStay - Exceptional Stays. Exceptional Returns.')
@section('description', 'Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay. Premium property rentals in Saudi Arabia.')

@section('content')
{{-- Hero Section - Dynamically Loaded --}}
<section id="hero-section" class="relative min-h-screen flex items-center justify-center overflow-hidden">
    {{-- Background Image with Parallax Effect --}}
    <div class="absolute inset-0 z-0">
        <img src="/assets/images/hero-riyadh-skyline.jpg" alt="Riyadh Skyline" 
            class="w-full h-full object-cover" 
            style="transform: translateZ(0); will-change: transform;"
            data-parallax="0.5">
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-black/50"></div>
    </div>
    
    {{-- Hero Content --}}
    <div class="relative z-10 container mx-auto px-4 text-center text-white">
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 animate-fade-in-up">
            {{ $heroContent->title ?? 'Exceptional Stays. Exceptional Returns.' }}
        </h1>
        <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto animate-fade-in-up animation-delay-200">
            {{ $heroContent->subtitle ?? 'Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay.' }}
        </p>
        
        {{-- CTA Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-fade-in-up animation-delay-400">
            <a href="/stays" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all transform hover:scale-105 hover:shadow-xl">
                <i class="fas fa-search mr-2"></i> Book a Stay
            </a>
            <a href="/host/properties/create" class="bg-white hover:bg-gray-100 text-gray-900 px-8 py-4 rounded-lg font-semibold text-lg transition-all transform hover:scale-105 hover:shadow-xl">
                <i class="fas fa-home mr-2"></i> List Property
            </a>
            <a href="/invest" class="border-2 border-white hover:bg-white hover:text-gray-900 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all transform hover:scale-105">
                <i class="fas fa-chart-line mr-2"></i> Invest Now
            </a>
        </div>
        
        {{-- Quick Search Bar --}}
        <div class="bg-white rounded-2xl shadow-2xl p-2 max-w-4xl mx-auto animate-fade-in-up animation-delay-600">
            <form action="/stays" method="GET" class="flex flex-col lg:flex-row gap-2">
                <div class="flex-1 relative">
                    <i class="fas fa-map-marker-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="location" placeholder="Where do you want to go?" 
                        class="w-full pl-12 pr-4 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                <div class="flex gap-2">
                    <div class="relative">
                        <i class="fas fa-calendar absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="date" name="check_in" placeholder="Check-in" 
                            class="pl-12 pr-4 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div class="relative">
                        <i class="fas fa-calendar absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="date" name="check_out" placeholder="Check-out" 
                            class="pl-12 pr-4 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div class="relative">
                        <i class="fas fa-users absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <select name="guests" class="pl-12 pr-8 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 appearance-none">
                            <option value="2">2 Guests</option>
                            <option value="1">1 Guest</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5+ Guests</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-4 rounded-xl font-semibold transition-all transform hover:scale-105">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </form>
        </div>
    </div>
    
    {{-- Scroll Indicator --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fas fa-chevron-down text-white text-2xl"></i>
    </div>
</section>

{{-- Why HabibiStay Section --}}
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ $whySection->title ?? 'Why HabibiStay?' }}</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {{ $whySection->subtitle ?? 'Discover the HabibiStay difference, tailored for everyone:' }}
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Guests Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="bg-purple-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-suitcase text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Guests</h3>
                <p class="text-purple-600 font-semibold mb-4">Five‑star comfort, local flair.</p>
                <p class="text-gray-600">
                    Experience Riyadh like never before. Our stays blend five-star comfort with authentic local flair, ensuring every trip is memorable.
                </p>
                <ul class="mt-6 space-y-2">
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Verified Premium Properties
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        24/7 Guest Support
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Instant Booking Available
                    </li>
                </ul>
            </div>
            
            {{-- Property Owners Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="bg-green-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-key text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Property Owners</h3>
                <p class="text-green-600 font-semibold mb-4">Hands‑off income, expert care.</p>
                <p class="text-gray-600">
                    Maximize your rental income effortlessly. We provide comprehensive property management, from guest booking to maintenance, so you enjoy peace of mind.
                </p>
                <ul class="mt-6 space-y-2">
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Professional Photography
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Dynamic Pricing Optimization
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Full Property Management
                    </li>
                </ul>
            </div>
            
            {{-- Investors Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="bg-blue-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Investors</h3>
                <p class="text-blue-600 font-semibold mb-4">Reliable, inflation‑hedged returns.</p>
                <p class="text-gray-600">
                    Tap into Riyadh's thriving real estate market. We offer secure investment opportunities designed for robust, inflation-hedged returns.
                </p>
                <ul class="mt-6 space-y-2">
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        15%+ Target Annual Returns
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Quarterly Distributions
                    </li>
                    <li class="flex items-center text-gray-700">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Full Transparency & Reporting
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Featured Properties Section --}}
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold mb-2">Discover Our Featured Properties</h2>
                <p class="text-xl text-gray-600">Hand-picked luxury stays in prime locations</p>
            </div>
            <a href="/stays" class="hidden md:flex items-center text-purple-600 hover:text-purple-700 font-semibold">
                See All Stays <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($featuredProperties as $property)
            <div class="group cursor-pointer" onclick="window.location.href='/properties/{{ $property->slug }}'">
                <div class="relative overflow-hidden rounded-2xl mb-4">
                    <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" 
                        class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-500">
                    
                    {{-- Wishlist Button --}}
                    <button onclick="event.stopPropagation(); toggleWishlist({{ $property->id }})" 
                        class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm p-2 rounded-full hover:bg-white transition-all">
                        <i class="far fa-heart text-gray-700 hover:text-red-500"></i>
                    </button>
                    
                    {{-- Price Badge --}}
                    <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                        <span class="font-bold">{{ number_format($property->price_per_night) }} SAR</span>
                        <span class="text-sm text-gray-600">/night</span>
                    </div>
                    
                    @if($property->instant_booking)
                    <div class="absolute top-4 left-4 bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                        <i class="fas fa-bolt mr-1"></i> Instant Book
                    </div>
                    @endif
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xl font-bold group-hover:text-purple-600 transition-colors">{{ $property->title }}</h3>
                        @if($property->overall_rating)
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-1"></i>
                            <span class="font-semibold">{{ number_format($property->overall_rating, 1) }}</span>
                            <span class="text-gray-500 text-sm ml-1">({{ $property->review_count }})</span>
                        </div>
                        @endif
                    </div>
                    <p class="text-gray-600 mb-2">{{ $property->city }}, {{ $property->neighborhood }}</p>
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span><i class="fas fa-users mr-1"></i> {{ $property->accommodates }} guests</span>
                        <span><i class="fas fa-bed mr-1"></i> {{ $property->bedrooms }} bedrooms</span>
                        <span><i class="fas fa-bath mr-1"></i> {{ $property->bathrooms }} bath</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-12 md:hidden">
            <a href="/stays" class="inline-flex items-center text-purple-600 hover:text-purple-700 font-semibold">
                See All Stays <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

{{-- How It Works Section --}}
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">How HabibiStay Works</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Simple, transparent, and designed for your success
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            {{-- For Guests --}}
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-6 text-purple-600">For Guests</h3>
                <div class="space-y-8">
                    <div class="relative">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-purple-600">1</span>
                        </div>
                        <h4 class="font-semibold mb-2">Search & Discover</h4>
                        <p class="text-gray-600">Browse verified luxury properties with detailed photos and amenities</p>
                    </div>
                    <div class="relative">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-purple-600">2</span>
                        </div>
                        <h4 class="font-semibold mb-2">Book Instantly</h4>
                        <p class="text-gray-600">Secure your stay with instant booking or chat with Sara for assistance</p>
                    </div>
                    <div class="relative">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-purple-600">3</span>
                        </div>
                        <h4 class="font-semibold mb-2">Enjoy Your Stay</h4>
                        <p class="text-gray-600">Experience premium comfort with 24/7 support throughout your trip</p>
                    </div>
                </div>
            </div>
            
            {{-- For Hosts --}}
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-6 text-green-600">For Hosts</h3>
                <div class="space-y-8">
                    <div class="relative">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-green-600">1</span>
                        </div>
                        <h4 class="font-semibold mb-2">List Your Property</h4>
                        <p class="text-gray-600">Quick onboarding with professional photography included</p>
                    </div>
                    <div class="relative">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-green-600">2</span>
                        </div>
                        <h4 class="font-semibold mb-2">We Manage Everything</h4>
                        <p class="text-gray-600">From bookings to cleaning, maintenance to guest support</p>
                    </div>
                    <div class="relative">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-green-600">3</span>
                        </div>
                        <h4 class="font-semibold mb-2">Earn Monthly</h4>
                        <p class="text-gray-600">Receive guaranteed monthly payouts with full transparency</p>
                    </div>
                </div>
            </div>
            
            {{-- For Investors --}}
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-6 text-blue-600">For Investors</h3>
                <div class="space-y-8">
                    <div class="relative">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-blue-600">1</span>
                        </div>
                        <h4 class="font-semibold mb-2">Choose Investment</h4>
                        <p class="text-gray-600">Select from curated opportunities or pool investments</p>
                    </div>
                    <div class="relative">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-blue-600">2</span>
                        </div>
                        <h4 class="font-semibold mb-2">We Operate</h4>
                        <p class="text-gray-600">Professional management maximizes property performance</p>
                    </div>
                    <div class="relative">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-blue-600">3</span>
                        </div>
                        <h4 class="font-semibold mb-2">Earn Returns</h4>
                        <p class="text-gray-600">Quarterly distributions with detailed performance reports</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials Section --}}
<section class="py-20 bg-white overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Real Results, Real People</h2>
            <p class="text-xl text-gray-600">Don't just take our word for it</p>
        </div>
        
        <div class="relative">
            <div class="flex gap-8 animate-scroll-x">
                @foreach($testimonials as $testimonial)
                <div class="bg-gray-50 rounded-2xl p-8 min-w-[350px] md:min-w-[400px]">
                    <div class="flex items-center mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <i class="fas fa-star text-yellow-500"></i>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-6 italic">"{{ $testimonial->content }}"</p>
                    <div class="flex items-center">
                        <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}" 
                            class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <p class="font-semibold">{{ $testimonial->name }}</p>
                            <p class="text-sm text-gray-600">{{ $testimonial->role }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Experience HabibiStay?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Whether you're looking for your next getaway, want to list your property, or explore investment opportunities, we're here to help.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/register" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105">
                Get Started Today
            </a>
            <button onclick="openSaraChat()" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-purple-600 transition-all transform hover:scale-105">
                <i class="fas fa-robot mr-2"></i> Chat with Sara
            </button>
        </div>
    </div>
</section>

{{-- Newsletter Section --}}
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 max-w-4xl mx-auto">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <h3 class="text-2xl font-bold mb-4">Stay Updated</h3>
                    <p class="text-gray-600">Get exclusive deals, investment opportunities, and travel tips delivered to your inbox.</p>
                </div>
                <form class="flex gap-2">
                    <input type="email" placeholder="Enter your email" required
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Animation Classes */
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out forwards;
    }
    
    .animation-delay-200 {
        animation-delay: 200ms;
    }
    
    .animation-delay-400 {
        animation-delay: 400ms;
    }
    
    .animation-delay-600 {
        animation-delay: 600ms;
    }
    
    /* Horizontal Scroll Animation */
    @keyframes scroll-x {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }
    
    .animate-scroll-x {
        animation: scroll-x 30s linear infinite;
    }
    
    /* Parallax Effect */
    [data-parallax] {
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@push('scripts')
<script>
    // Parallax Effect
    document.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.parallax;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
    
    // Intersection Observer for Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all sections
    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });
    
    // Wishlist Toggle
    async function toggleWishlist(propertyId) {
        try {
            const response = await axios.post(`/api/properties/${propertyId}/wishlist`);
            showToast(response.data.message, 'success');
            
            // Update heart icon
            event.target.classList.toggle('far');
            event.target.classList.toggle('fas');
            event.target.classList.toggle('text-red-500');
        } catch (error) {
            if (error.response?.status === 401) {
                window.location.href = '/login';
            } else {
                showToast('Failed to update wishlist', 'error');
            }
        }
    }
    
    // Dynamic Content Loading
    document.addEventListener('DOMContentLoaded', () => {
        // Load dynamic content if admin has customized
        loadDynamicContent();
    });
    
    async function loadDynamicContent() {
        try {
            const response = await axios.get('/api/content/homepage');
            if (response.data.data) {
                // Update hero content
                if (response.data.data.hero) {
                    document.querySelector('#hero-section h1').textContent = response.data.data.hero.title;
                    document.querySelector('#hero-section p').textContent = response.data.data.hero.subtitle;
                }
                // Update other sections as needed
            }
        } catch (error) {
            console.error('Failed to load dynamic content:', error);
        }
    }
</script>
@endpush
