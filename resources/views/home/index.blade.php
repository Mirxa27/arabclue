@extends('layouts.app')

@section('title', 'HabibiStay - Exceptional Stays, Exceptional Returns')

@section('content')
{{-- Hero Section with Dynamic Background --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    {{-- Background Image with Lazy Loading --}}
    <div class="absolute inset-0 z-0">
        <picture>
            <source media="(max-width: 640px)" srcset="/assets/images/hero-mobile.webp">
            <source media="(max-width: 1024px)" srcset="/assets/images/hero-tablet.webp">
            <img src="/assets/images/hero-desktop.webp" 
                 alt="Luxury Property in Riyadh" 
                 class="w-full h-full object-cover"
                 loading="eager">
        </picture>
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-transparent"></div>
    </div>
    
    {{-- Hero Content --}}
    <div class="relative z-10 container mx-auto px-4 py-16 lg:py-24 text-white">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- Left Column: Text and CTAs --}}
            <div class="text-center lg:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 animate-fade-in-up" style="color: #ffffff;">
                    Meet Sara,<br>Your AI Booking Assistant
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-xl mx-auto lg:mx-0 animate-fade-in-up animation-delay-200" style="color: #f0f0f0;">
                    Let Sara help you find the perfect stay, manage bookings, and get support—all through chat.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-fade-in-up animation-delay-400">
                    <a href="#sara-chatbot" onclick="document.getElementById('sara-chat-input').focus(); return false;" class="bg-white text-brand-blue px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-comments mr-2"></i>Chat with Sara
                    </a>
                    <a href="/stays" class="bg-transparent text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all border-2 border-white">
                        <i class="fas fa-search mr-2"></i>Browse Properties
                    </a>
                </div>
            </div>

            {{-- Right Column: Sara Chatbot Placeholder --}}
            <div id="sara-chatbot" class="bg-white/20 backdrop-blur-lg p-6 rounded-xl shadow-2xl animate-fade-in-up animation-delay-600 flex flex-col" style="height: 500px;"> {{-- Adjusted height --}}
                <h2 class="text-2xl font-bold mb-4 text-white text-center">AI Booking Assistant</h2>
                <div id="chat-messages-container" class="bg-white rounded-lg p-4 flex-grow overflow-y-auto flex flex-col space-y-3 mb-4">
                    {{-- Chat messages will be dynamically inserted here by JavaScript --}}
                </div>
                <div class="mt-auto"> {{-- Pushes input to bottom --}}
                    <div class="flex items-center">
                        <input id="sara-chat-input" type="text" placeholder="Type your message or use voice..." class="w-full p-3 rounded-l-lg border border-gray-300 text-gray-800 focus:ring-brand-blue focus:border-brand-blue" disabled>
                        <button id="sara-send-btn" class="bg-brand-blue text-white p-3 rounded-r-lg hover:opacity-90 transition-opacity" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        {{-- Voice input button can be added here later --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Scroll Indicator --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fas fa-chevron-down text-white text-2xl"></i>
    </div>
</section>

{{-- Sara's Featured Properties Section --}}
<section class="py-12 lg:py-16 bg-gray-100" id="sara-featured-properties">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-3xl lg:text-4xl font-bold mb-3">Sara Recommends</h2>
            <p class="text-lg text-gray-600">A couple of great options to get you started!</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Placeholder for Property 1 --}}
            <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                <img src="https://via.placeholder.com/600x400.png?text=Luxury+Apartment" alt="Featured Property 1" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Luxury Apartment in City Center</h3>
                    <p class="text-gray-600 mb-1"><i class="fas fa-map-marker-alt mr-2 text-brand-blue"></i>Downtown Riyadh</p>
                    <p class="text-gray-600 mb-4"><i class="fas fa-users mr-2 text-brand-blue"></i>Sleeps 4 | <i class="fas fa-bed ml-2 mr-2 text-brand-blue"></i>2 Bedrooms</p>
                    <p class="text-2xl font-bold text-brand-blue mb-4">SAR 500 <span class="text-sm text-gray-600 font-normal">/ night</span></p>
                    <a href="#" class="bg-brand-blue text-white px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity block text-center">
                        View Details & Book
                    </a>
                </div>
            </div>
            {{-- Placeholder for Property 2 --}}
            <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                <img src="https://via.placeholder.com/600x400.png?text=Cozy+Villa" alt="Featured Property 2" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Cozy Villa with Garden</h3>
                    <p class="text-gray-600 mb-1"><i class="fas fa-map-marker-alt mr-2 text-brand-blue"></i>Al Malqa District</p>
                    <p class="text-gray-600 mb-4"><i class="fas fa-users mr-2 text-brand-blue"></i>Sleeps 6 | <i class="fas fa-bed ml-2 mr-2 text-brand-blue"></i>3 Bedrooms</p>
                    <p class="text-2xl font-bold text-brand-blue mb-4">SAR 750 <span class="text-sm text-gray-600 font-normal">/ night</span></p>
                    <a href="#" class="bg-brand-blue text-white px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity block text-center">
                        View Details & Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Why HabibiStay Section with Intersection Observer --}}
<section class="py-16 lg:py-24 bg-white" id="why-habibistay">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 intersection-observer fade-in-up">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">Discover the HabibiStay Difference</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Tailored solutions for guests, property owners, and investors
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Guests Card --}}
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 intersection-observer fade-in-up">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-suitcase text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">For Guests</h3>
                <p class="text-lg font-medium text-purple-600 mb-4">Five‑star comfort, local flair.</p>
                <p class="text-gray-600 mb-6">
                    Experience Riyadh like never before. Our stays blend five-star comfort with authentic local flair, ensuring every trip is memorable.
                </p>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-1"></i>
                        <span>Curated luxury properties</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-1"></i>
                        <span>24/7 concierge support</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-1"></i>
                        <span>Instant booking available</span>
                    </li>
                </ul>
            </div>
            
            {{-- Property Owners Card --}}
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 intersection-observer fade-in-up animation-delay-200">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-key text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">For Property Owners</h3>
                <p class="text-lg font-medium text-green-600 mb-4">Hands‑off income, expert care.</p>
                <p class="text-gray-600 mb-6">
                    Maximize your rental income effortlessly. We provide comprehensive property management, from guest booking to maintenance.
                </p>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                        <span>Average 76% NOI increase</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                        <span>Full-service management</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                        <span>Monthly transparent payouts</span>
                    </li>
                </ul>
            </div>
            
            {{-- Investors Card --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 intersection-observer fade-in-up animation-delay-400">
                <div class="bg-amber-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-chart-line text-amber-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">For Investors</h3>
                <p class="text-lg font-medium text-amber-600 mb-4">Reliable, inflation‑hedged returns.</p>
                <p class="text-gray-600 mb-6">
                    Tap into Riyadh's thriving real estate market. We offer secure investment opportunities designed for robust returns.
                </p>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-amber-600 mr-2 mt-1"></i>
                        <span>15%+ targeted IRR</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-amber-600 mr-2 mt-1"></i>
                        <span>Quarterly dividends</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-amber-600 mr-2 mt-1"></i>
                        <span>Professional asset management</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Featured Properties with Virtual Scrolling --}}
<section class="py-16 lg:py-24 bg-gray-50" id="featured-properties">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 intersection-observer fade-in-up">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">Discover Our Featured Properties</h2>
            <p class="text-xl text-gray-600">Handpicked luxury stays in prime Riyadh locations</p>
        </div>
        
        {{-- Property Grid with Skeleton Loading --}}
        <div id="property-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Properties will be loaded dynamically --}}
            @for($i = 0; $i < 6; $i++)
            <div class="property-skeleton">
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg">
                    <div class="skeleton h-64 w-full"></div>
                    <div class="p-6">
                        <div class="skeleton h-6 w-3/4 mb-3"></div>
                        <div class="skeleton h-4 w-1/2 mb-4"></div>
                        <div class="skeleton h-8 w-full"></div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
        
        <div class="text-center mt-12">
            <a href="/stays" class="bg-purple-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-purple-700 transition-all inline-block">
                See All Stays <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

{{-- Testimonials with Carousel --}}
<section class="py-16 lg:py-24 bg-white overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 intersection-observer fade-in-up">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">Real Results, Real People</h2>
            <p class="text-xl text-gray-600">Don't just take our word for it</p>
        </div>
        
        {{-- Testimonial Carousel --}}
        <div class="relative">
            <div class="testimonial-carousel flex gap-8 transition-transform duration-500">
                {{-- Owner Testimonial --}}
                <div class="w-full md:w-1/3 flex-shrink-0">
                    <div class="bg-purple-50 rounded-2xl p-8 h-full">
                        <div class="flex mb-4">
                            @for($i = 0; $i < 5; $i++)
                            <i class="fas fa-star text-yellow-400"></i>
                            @endfor
                        </div>
                        <p class="text-lg mb-6 italic">
                            "HabibiStay doubled my rental income in six months. Their professionalism and market knowledge are unmatched."
                        </p>
                        <div class="flex items-center">
                            <img src="/assets/images/testimonial-1.jpg" alt="Ahmed" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold">Ahmed M.</p>
                                <p class="text-sm text-gray-600">Property Owner, Riyadh</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Investor Testimonial --}}
                <div class="w-full md:w-1/3 flex-shrink-0">
                    <div class="bg-green-50 rounded-2xl p-8 h-full">
                        <div class="flex mb-4">
                            @for($i = 0; $i < 5; $i++)
                            <i class="fas fa-star text-yellow-400"></i>
                            @endfor
                        </div>
                        <p class="text-lg mb-6 italic">
                            "I achieved a 15% IRR in my first year. Their transparent reporting keeps me confident in my investment."
                        </p>
                        <div class="flex items-center">
                            <img src="/assets/images/testimonial-2.jpg" alt="Fatima" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold">Fatima A.</p>
                                <p class="text-sm text-gray-600">Investor</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Guest Testimonial --}}
                <div class="w-full md:w-1/3 flex-shrink-0">
                    <div class="bg-amber-50 rounded-2xl p-8 h-full">
                        <div class="flex mb-4">
                            @for($i = 0; $i < 5; $i++)
                            <i class="fas fa-star text-yellow-400"></i>
                            @endfor
                        </div>
                        <p class="text-lg mb-6 italic">
                            "Every stay feels like home. The attention to detail and local touches make HabibiStay my go-to choice."
                        </p>
                        <div class="flex items-center">
                            <img src="/assets/images/testimonial-3.jpg" alt="Carlos" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold">Carlos G.</p>
                                <p class="text-sm text-gray-600">Frequent Guest</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Carousel Controls --}}
            <button onclick="moveCarousel(-1)" class="absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition-all">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="moveCarousel(1)" class="absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition-all">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

{{-- How It Works Section --}}
<section class="py-16 lg:py-24 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 intersection-observer fade-in-up">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">How It Works</h2>
            <p class="text-xl text-gray-600">Simple, transparent, and efficient</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Step 1 --}}
            <div class="text-center intersection-observer fade-in-up">
                <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                    <span class="text-2xl font-bold text-purple-600">1</span>
                    <div class="hidden md:block absolute left-full top-1/2 w-full h-0.5 bg-purple-200"></div>
                </div>
                <h3 class="text-xl font-bold mb-3">Choose Your Path</h3>
                <p class="text-gray-600">Whether you're a guest seeking luxury stays, an owner looking to maximize income, or an investor seeking returns.</p>
            </div>
            
            {{-- Step 2 --}}
            <div class="text-center intersection-observer fade-in-up animation-delay-200">
                <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                    <span class="text-2xl font-bold text-purple-600">2</span>
                    <div class="hidden md:block absolute left-full top-1/2 w-full h-0.5 bg-purple-200"></div>
                </div>
                <h3 class="text-xl font-bold mb-3">We Handle Everything</h3>
                <p class="text-gray-600">From booking to property management to investment oversight, our expert team manages all the details.</p>
            </div>
            
            {{-- Step 3 --}}
            <div class="text-center intersection-observer fade-in-up animation-delay-400">
                <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-purple-600">3</span>
                </div>
                <h3 class="text-xl font-bold mb-3">Enjoy the Benefits</h3>
                <p class="text-gray-600">Memorable stays, steady income, or growing returns—whatever your goal, we deliver exceptional results.</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-16 lg:py-24 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold mb-6">Ready to Get Started?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Join thousands who trust HabibiStay for exceptional stays and exceptional returns.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/register" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                Get Started Today
            </a>
            <a href="/contact" class="bg-transparent text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all border-2 border-white">
                Contact Us
            </a>
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
    
    .animation-delay-200 { animation-delay: 200ms; }
    .animation-delay-400 { animation-delay: 400ms; }
    
    /* Intersection Observer Classes */
    .intersection-observer {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }
    
    .intersection-observer.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Property Skeleton */
    .property-skeleton {
        transition: opacity 0.3s ease-out;
    }
    
    .property-skeleton.loaded {
        opacity: 0;
        pointer-events: none;
    }
    .text-brand-blue { color: #2957c3 !important; } /* Use !important to ensure override if other general styles conflict */
    .bg-brand-blue { background-color: #2957c3 !important; }
    .focus\:ring-brand-blue:focus { --tw-ring-color: #2957c3; }
    .focus\:border-brand-blue:focus { border-color: #2957c3; }
</style>
@endpush

@push('scripts')
<script>
    // Intersection Observer for Scroll Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.intersection-observer').forEach(el => {
        observer.observe(el);
    });
    
    // Dynamic Property Loading with Virtual DOM Rendering
    async function loadFeaturedProperties() {
        try {
            const response = await axios.get('/api/v1/properties/featured');
            const properties = response.data.data.properties;
            const container = document.getElementById('property-grid');
            
            // Create document fragment for better performance
            const fragment = document.createDocumentFragment();
            
            properties.forEach((property, index) => {
                const propertyCard = createPropertyCard(property);
                fragment.appendChild(propertyCard);
            });
            
            // Replace skeleton loaders with actual content
            container.innerHTML = '';
            container.appendChild(fragment);
            
        } catch (error) {
            console.error('Failed to load properties:', error);
            // Assuming showToast is defined elsewhere or replace with console.log/alert
            // showToast('Failed to load properties', 'error');
            console.error('Toast: Failed to load properties (error)');
        }
    }
    
    // Optimized Property Card Creation
    function createPropertyCard(property) {
        const article = document.createElement('article');
        article.className = 'bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2';
        article.innerHTML = `
            <a href="/stays/${property.slug}" class="block">
                <div class="relative h-64 overflow-hidden">
                    <img src="${property.image}" 
                         alt="${property.title}" 
                         class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500"
                         loading="lazy">
                    ${property.features.is_featured ? '<span class="absolute top-4 left-4 bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium">Featured</span>' : ''}
                    <button onclick="toggleWishlist(event, ${property.id})" class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm p-2 rounded-full hover:bg-white transition-all">
                        <i class="fas fa-heart text-gray-400 hover:text-red-500 transition-colors"></i>
                    </button>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">${property.title}</h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt mr-1"></i> ${property.location.neighborhood}, ${property.location.city}
                    </p>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users mr-1"></i> ${property.capacity.guests} guests
                            <span class="mx-2">•</span>
                            <i class="fas fa-bed mr-1"></i> ${property.capacity.bedrooms} bed
                        </div>
                        ${property.rating.average > 0 ? `
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <span class="font-medium">${property.rating.average}</span>
                            <span class="text-gray-500 text-sm ml-1">(${property.rating.count})</span>
                        </div>` : ''}
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-2xl font-bold text-purple-600">
                            ${property.price.formatted}
                            <span class="text-sm text-gray-600 font-normal">/ night</span>
                        </p>
                        ${property.features.instant_booking ? 
                            '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Instant Book</span>' : ''}
                    </div>
                </div>
            </a>
        `;
        return article;
    }
    
    // Carousel Functionality with Touch Support
    let carouselPosition = 0;
    const carousel = document.querySelector('.testimonial-carousel');
    const carouselItems = carousel ? carousel.children.length : 0; // Added null check
    
    function moveCarousel(direction) {
        if (!carousel || carouselItems === 0) return; // Added null check
        const itemWidth = carousel.children[0].offsetWidth + 32; // Width + gap
        carouselPosition = Math.max(0, Math.min(carouselPosition + direction, carouselItems - 1));
        carousel.style.transform = `translateX(-${carouselPosition * itemWidth}px)`;
    }
    
    // Touch support for carousel
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (carousel) { // Added null check
        carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        carousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) moveCarousel(1);
        if (touchEndX > touchStartX + 50) moveCarousel(-1);
    }
    
    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('property-grid')) { // Check if element exists
            loadFeaturedProperties(); // This loads properties for the non-chat section
        }
        if (document.getElementById('sara-chatbot')) { // Check if element exists
            initiateSaraChat(); // Initialize Sara Chatbot interface
        }
        
        // Preload critical images
        const criticalImages = [
            '/assets/images/hero-desktop.webp',
            '/assets/images/hero-mobile.webp'
        ];
        
        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });

        const chatInput = document.getElementById('sara-chat-input');
        const sendButton = document.getElementById('sara-send-btn');

        if (chatInput) {
            chatInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    sendMessageToSara();
                }
            });
        }
        if (sendButton) {
            sendButton.addEventListener('click', sendMessageToSara);
        }
    });

    let saraConversationId = null;

    async function initiateSaraChat() {
        const chatMessagesContainer = document.getElementById('chat-messages-container');
        const chatInput = document.getElementById('sara-chat-input');
        const sendButton = document.getElementById('sara-send-btn');

        if (!chatMessagesContainer) {
            console.error("Critical: chat-messages-container not found!");
            return;
        }
        console.log("Initiating Sara Chat. Clearing container...");
        chatMessagesContainer.innerHTML = ''; 

        appendMessageToChat("Sara", "Connecting to Sara...", 'sara-message', true); 

        try {
            console.log("Attempting to fetch initial conversation data (mocked)...");
            await new Promise(resolve => setTimeout(resolve, 500)); 
            const mockApiResponse = { 
                data: {
                    success: true,
                    greeting: "Welcome to HabibiStay! I'm Sara, your AI assistant. Here are a couple of our featured properties to get you started:",
                    featured_properties: [
                        { id: 1, title: "Luxury Apartment Downtown", description: "Stunning views, modern amenities.", price_per_night: 500, formatted_price: "SAR 500", property_type: "Apartment", accommodates: 4, bedrooms: 2, bathrooms: 2, image: "https://via.placeholder.com/600x400.png?text=Luxury+Apt", location: "Downtown, Riyadh", rating: 4.8, slug: "luxury-apartment-downtown" },
                        { id: 2, title: "Cozy Villa with Garden", description: "Perfect for families, private garden.", price_per_night: 750, formatted_price: "SAR 750", property_type: "Villa", accommodates: 6, bedrooms: 3, bathrooms: 2.5, image: "https://via.placeholder.com/600x400.png?text=Cozy+Villa", location: "Al Malqa, Riyadh", rating: 4.9, slug: "cozy-villa-garden" }
                    ],
                    conversation_id: 'conv_' + Date.now(),
                    action_buttons: [
                        {label: 'Explore Other Properties', action: 'explore_other'},
                        {label: 'Help & Support', action: 'help_support'}
                    ]
                }
            };
            console.log("Mock API Response received:", JSON.stringify(mockApiResponse, null, 2));
            
            const connectingMessage = chatMessagesContainer.querySelector('.system-message');
            if (connectingMessage) {
                chatMessagesContainer.removeChild(connectingMessage);
            }

            if (mockApiResponse && mockApiResponse.data && mockApiResponse.data.success) {
                saraConversationId = mockApiResponse.data.conversation_id;
                console.log("Sara Conversation ID set:", saraConversationId);

                const greeting = mockApiResponse.data.greeting;
                if (typeof greeting === 'string' && greeting.trim() !== '') {
                    appendMessageToChat("Sara", greeting, 'sara-message');
                } else {
                    console.warn("Greeting message is missing or invalid. Using fallback.");
                    appendMessageToChat("Sara", "Hello! How can I help you today?", 'sara-message');
                }

                const featuredProperties = mockApiResponse.data.featured_properties;
                if (Array.isArray(featuredProperties) && featuredProperties.length > 0) {
                    console.log("Processing featured properties:", featuredProperties);
                    featuredProperties.forEach((property, index) => {
                        console.log(`Processing property ${index + 1}:`, property);
                        if (property && typeof property.title === 'string') { 
                           appendPropertyCardToChat(property);
                        } else {
                            console.warn(`Invalid data for property at index ${index}:`, property);
                        }
                    });
                } else {
                    console.log("No featured properties found in response or array is empty.");
                }

                const actionButtons = mockApiResponse.data.action_buttons;
                if (Array.isArray(actionButtons) && actionButtons.length > 0) {
                    console.log("Processing action buttons:", actionButtons);
                    appendActionButtonsToChat(actionButtons);
                } else {
                    console.log("No action buttons found in response.");
                }

                if(chatInput) chatInput.disabled = false;
                if(sendButton) sendButton.disabled = false;
                console.log("Chat initialized successfully. Input enabled.");
            } else {
                const errorMessage = (mockApiResponse && mockApiResponse.data && mockApiResponse.data.message) ? mockApiResponse.data.message : "Sorry, I couldn't start our conversation right now. Please try again later.";
                console.error("API call not successful or data malformed. Error:", errorMessage, "Full response:", mockApiResponse);
                appendMessageToChat("Sara", errorMessage, 'sara-message error-message');
            }
        } catch (error) {
            console.error('Critical error in initiateSaraChat:', error);
            const connectingMessage = chatMessagesContainer.querySelector('.system-message');
            if (connectingMessage) {
                chatMessagesContainer.removeChild(connectingMessage);
            }
            appendMessageToChat("Sara", "Oops! A critical error occurred while connecting. Please refresh and try again.", 'sara-message error-message');
        }
    }

    function appendMessageToChat(sender, text, type = 'user-message', isSystemMessage = false) {
        const chatMessagesContainer = document.getElementById('chat-messages-container');
        if (!chatMessagesContainer) {
            console.error("appendMessageToChat: chat-messages-container not found!");
            return;
        }
        const messageDiv = document.createElement('div');
        const messageText = (typeof text === 'string' || typeof text === 'number') ? String(text) : "[message content unavailable]";

        messageDiv.classList.add('p-3', 'rounded-lg', 'max-w-xs', 'lg:max-w-md', 'chat-message-item', 'mb-2'); 
        
        if (isSystemMessage) {
            messageDiv.classList.add('system-message', 'text-center', 'text-xs', 'text-gray-500', 'w-full', 'max-w-full', 'self-center');
            messageDiv.textContent = messageText;
        } else if (type === 'sara-message') {
            messageDiv.classList.add('bg-gray-100', 'self-start');
            const senderSpan = document.createElement('span');
            senderSpan.className = 'font-semibold text-brand-blue';
            senderSpan.textContent = sender + ": ";
            messageDiv.appendChild(senderSpan);
            
            const textNode = document.createElement('p');
            textNode.className = 'text-sm text-gray-700 inline';
            textNode.textContent = messageText;
            messageDiv.appendChild(textNode);

        } else { // user message
            messageDiv.classList.add('bg-brand-blue', 'text-white', 'self-end');
            const textNode = document.createElement('p');
            textNode.className = 'text-sm text-white';
            textNode.textContent = messageText;
            messageDiv.appendChild(textNode);
        }
        
        chatMessagesContainer.appendChild(messageDiv);
        chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
    }

    function appendPropertyCardToChat(property) {
        const chatMessagesContainer = document.getElementById('chat-messages-container');
        if (!chatMessagesContainer) {
            console.error("appendPropertyCardToChat: chat-messages-container not found!");
            return;
        }
        const cardDiv = document.createElement('div');
        cardDiv.className = 'sara-property-card bg-gray-50 p-3 rounded-lg self-start max-w-xs lg:max-w-sm my-2 shadow';
        
        const imageUrl = property.image || "https://via.placeholder.com/300x200.png?text=No+Image";
        const title = property.title || "Untitled Property";
        const location = property.location || "Location unknown";
        const formattedPrice = property.formatted_price || "Price unavailable";
        const slug = property.slug || `property-${property.id || Date.now()}`;
        const id = property.id || `unknown-${Date.now()}`;

        cardDiv.innerHTML = `
            <img src="${imageUrl}" alt="${title}" class="w-full h-32 object-cover rounded-md mb-2">
            <h4 class="font-semibold text-sm text-brand-blue mb-1">${title}</h4>
            <p class="text-xs text-gray-600 mb-1"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>${location}</p>
            <p class="text-xs text-gray-700 font-medium mb-2">${formattedPrice} <span class="font-normal text-gray-500">/ night</span></p>
            <button class="text-xs bg-brand-blue text-white px-3 py-2 rounded-md hover:opacity-90 w-full view-details-btn" data-slug="${slug}" data-property-id="${id}">
                View Details
            </button>
        `;
        chatMessagesContainer.appendChild(cardDiv);
        const viewDetailsButton = cardDiv.querySelector('.view-details-btn');
        if (viewDetailsButton) {
            viewDetailsButton.addEventListener('click', function() {
                handleChatAction('view_property_details', { slug: this.dataset.slug, id: this.dataset.propertyId });
            });
        }
        chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
    }

    function appendActionButtonsToChat(buttons) {
        const chatMessagesContainer = document.getElementById('chat-messages-container');
         if (!chatMessagesContainer) {
            console.error("appendActionButtonsToChat: chat-messages-container not found!");
            return;
        }
        const buttonsContainer = document.createElement('div');
        buttonsContainer.className = 'sara-action-buttons self-start mt-1 flex flex-wrap gap-2 py-2';
        
        buttons.forEach(buttonInfo => {
            if (!buttonInfo || typeof buttonInfo.label !== 'string' || typeof buttonInfo.action !== 'string') {
                console.warn("Invalid button info received:", buttonInfo);
                return; 
            }
            const button = document.createElement('button');
            button.className = 'text-xs bg-white border border-brand-blue text-brand-blue px-3 py-2 rounded-full hover:bg-gray-50 transition-colors';
            button.textContent = buttonInfo.label;
            button.dataset.action = buttonInfo.action;
            button.addEventListener('click', function() {
                handleChatAction(this.dataset.action, { label: this.textContent });
            });
            buttonsContainer.appendChild(button);
        });
        chatMessagesContainer.appendChild(buttonsContainer);
        chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
    }

    async function sendMessageToSara() {
        const chatInput = document.getElementById('sara-chat-input');
        const message = chatInput.value.trim();

        if (!message || !saraConversationId) {
            console.log("sendMessageToSara: Message or conversation ID missing. Message not sent.");
            return;
        }

        appendMessageToChat("You", message, 'user-message');
        chatInput.value = '';
        if(chatInput) chatInput.disabled = true; 
        const sendButton = document.getElementById('sara-send-btn');
        if(sendButton) sendButton.disabled = true;

        appendMessageToChat("Sara", "Typing...", 'sara-message', true); 

        try {
            console.log("Sending message to Sara:", { conversation_id: saraConversationId, message: message });
            await new Promise(resolve => setTimeout(resolve, 1000)); 
            const mockApiResponse = { 
                data: {
                    success: true,
                    message: "Okay, I've received your message: '" + message + "'. How else can I assist you today?",
                }
            };
            console.log("Response from Sara:", mockApiResponse.data);

            const messagesContainer = document.getElementById('chat-messages-container');
            const typingIndicator = messagesContainer.querySelector('.system-message.self-start'); 
            if (typingIndicator && typingIndicator.textContent.includes("Typing...")) {
                messagesContainer.removeChild(typingIndicator);
            }

            if (mockApiResponse && mockApiResponse.data && mockApiResponse.data.success) {
                appendMessageToChat("Sara", mockApiResponse.data.message, 'sara-message');
            } else {
                const errorMessage = (mockApiResponse && mockApiResponse.data && mockApiResponse.data.message) ? mockApiResponse.data.message : "Sorry, I had trouble understanding that.";
                appendMessageToChat("Sara", errorMessage, 'sara-message error-message');
            }
        } catch (error) {
            console.error('Failed to send message to Sara:', error);
            const messagesContainer = document.getElementById('chat-messages-container');
            const typingIndicator = messagesContainer.querySelector('.system-message.self-start');
            if (typingIndicator && typingIndicator.textContent.includes("Typing...")) {
                messagesContainer.removeChild(typingIndicator);
            }
            appendMessageToChat("Sara", "Sorry, there was an error sending your message. Please try again.", 'sara-message error-message');
        } finally {
            if(chatInput) chatInput.disabled = false;
            const sendButton = document.getElementById('sara-send-btn');
            if(sendButton) sendButton.disabled = false;
            if(chatInput) chatInput.focus();
        }
    }

    function handleChatAction(action, data) {
        console.log("Handling chat action:", action, "with data:", data);
        let userMessage = "";
        if (action === 'view_property_details') {
            userMessage = `Tell me more about property ${data.slug || 'with ID ' + data.id}.`;
        } else if (data && typeof data.label === 'string') {
            userMessage = data.label;
        } else if (typeof action === 'string') {
            userMessage = action.replace(/_/g, ' '); 
        } else {
            console.warn("Could not determine user message for action:", action, data);
            userMessage = "Selected an option";
        }
        
        const chatInput = document.getElementById('sara-chat-input');
        if (chatInput) {
            chatInput.value = userMessage; 
            sendMessageToSara(); 
        } else {
            console.error("handleChatAction: sara-chat-input not found!");
        }
    }
    
    // Wishlist Toggle with Optimistic UI Update
    async function toggleWishlist(event, propertyId) {
        event.preventDefault();
        event.stopPropagation();
        
        const button = event.currentTarget;
        const icon = button.querySelector('i');
        
        // Optimistic UI update
        icon.classList.toggle('text-red-500');
        icon.classList.toggle('text-gray-400');
        
        try {
            // Assuming axios is available globally or replace with fetch
            // await axios.post(`/api/v1/properties/${propertyId}/wishlist`);
            console.log(`Wishlist toggled for property ${propertyId}. (Simulated API call)`);
            // Mocking success for now
            await new Promise(resolve => setTimeout(resolve, 300));


        } catch (error) {
            // Revert on error
            icon.classList.toggle('text-red-500');
            icon.classList.toggle('text-gray-400');
            
            if (error.response?.status === 401) {
                window.location.href = '/login';
            } else {
                // showToast('Failed to update wishlist', 'error');
                console.error(`Toast: Failed to update wishlist for property ${propertyId} (error)`);
            }
        }
    }
</script>
@endpush
