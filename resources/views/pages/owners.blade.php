<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Owners | HabibiStay</title>
    <meta name="description" content="Turn your property into a high-performing asset with HabibiStay's comprehensive property management services.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #2957c3;
            --brand-blue-light: #4a6cf7;
            --brand-blue-dark: #1e3a8a;
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
            min-height: 70vh;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--brand-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 16px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--brand-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
            margin: 0 auto 16px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">HabibiStay</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/stays" class="text-gray-700 hover:text-blue-600 transition-colors">Stays</a>
                    <a href="/host" class="text-blue-600 font-semibold">Owners</a>
                    <a href="/invest" class="text-gray-700 hover:text-blue-600 transition-colors">Invest</a>
                    <a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
                    <a href="/stories" class="text-gray-700 hover:text-blue-600 transition-colors">Stories</a>
                    <a href="/blog" class="text-gray-700 hover:text-blue-600 transition-colors">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-blue-600 transition-colors">Contact</a>
                    @auth
                        <a href="/profile" class="text-gray-700 hover:text-blue-600 transition-colors">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600 transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-700 hover:text-blue-600 transition-colors">Login</a>
                        <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Sign Up</a>
                    @endauth
                </div>
                <div class="md:hidden">
                    <button id="mobileMenuBtn" class="text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-white mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Turn Keys into
                    <span class="block">Cashflow.</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    We manage everything—so you earn without the hassle. Partner with HabibiStay and transform your property into a high-performing asset.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                    <a href="#get-started" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                        Start Earning →
                    </a>
                    <a href="/contact" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-semibold">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Partner Your Property with HabibiStay?</h2>
                <p class="text-xl text-gray-600">Experience the complete property management solution</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Full-Service Management & Maintenance</h3>
                    <p class="text-gray-600">From guest communication and check-ins to professional cleaning and round-the-clock maintenance, we handle all operational aspects.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Dynamic Pricing for Higher Revenue</h3>
                    <p class="text-gray-600">Our expert team utilizes market data and proprietary algorithms to optimize your pricing, maximizing occupancy and your rental income.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Transparent Owner Portal & Monthly Payouts</h3>
                    <p class="text-gray-600">Access detailed performance reports, booking calendars, and financial statements anytime through your dedicated owner portal. Enjoy reliable monthly payouts directly to your account.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Simple Process Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Getting Started is Easy</h2>
                <p class="text-xl text-gray-600">Three simple steps to start earning with your property</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="step-number">1</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Sign Up</h3>
                    <p class="text-gray-600">Tell us about your property. Our team will assess its potential and guide you through a seamless onboarding process.</p>
                </div>
                
                <div class="text-center">
                    <div class="step-number">2</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">We Manage</h3>
                    <p class="text-gray-600">We list your property, manage bookings, handle guest services, and ensure it's impeccably maintained.</p>
                </div>
                
                <div class="text-center">
                    <div class="step-number">3</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">You Earn</h3>
                    <p class="text-gray-600">Sit back and watch your rental income grow, with full transparency and consistent payouts.</p>
                </div>
            </div>
            
            <div class="text-center mt-12" id="get-started">
                <div class="bg-white rounded-2xl p-8 max-w-2xl mx-auto shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Ready to Get Started?</h3>
                    <form class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="text" placeholder="Your Name" required
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <input type="email" placeholder="Email Address" required
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="tel" placeholder="Phone Number" required
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <select required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Property Type</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="house">House</option>
                                    <option value="condo">Condo</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <input type="text" placeholder="Property Location (City, District)" required
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <textarea placeholder="Tell us about your property (optional)" rows="3"
                                      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            Start Earning →
                        </button>
                    </form>
                    <p class="text-sm text-gray-500 mt-4">Our team will contact you within 24 hours to discuss your property's potential.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <blockquote class="text-white">
                <p class="text-2xl md:text-3xl font-light italic mb-6">
                    "Partnering with HabibiStay was a game-changer. My Net Operating Income (NOI) increased by an incredible 76% in just 8 months, all without me lifting a finger. Their team handles everything perfectly."
                </p>
                <footer class="text-xl font-semibold">
                    — Ahmed M., Property Owner
                </footer>
            </blockquote>
            <div class="mt-8">
                <a href="/stories" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                    More Success Stories →
                </a>
            </div>
        </div>
    </section>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            alert('Mobile menu - to be implemented');
        });
    </script>
</body>
</html>
