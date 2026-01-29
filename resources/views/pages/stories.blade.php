<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success Stories | HabibiStay</title>
    <meta name="description" content="Real results from real people. Read success stories from property owners, investors, and guests who choose HabibiStay.">
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
            min-height: 50vh;
        }
        
        .story-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .story-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .story-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 80px;
            color: var(--brand-blue);
            opacity: 0.1;
            font-family: serif;
            line-height: 1;
        }
        
        .category-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .owner-badge {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .investor-badge {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .guest-badge {
            background: #fff3e0;
            color: #f57c00;
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
                    <a href="/host" class="text-gray-700 hover:text-blue-600 transition-colors">Owners</a>
                    <a href="/invest" class="text-gray-700 hover:text-blue-600 transition-colors">Invest</a>
                    <a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
                    <a href="/stories" class="text-blue-600 font-semibold">Stories</a>
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
                    Real Results,
                    <span class="block">Real People</span>
                </h1>
                <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
                    Don't just take our word for it. See how HabibiStay has made a difference for property owners, investors, and guests.
                </p>
            </div>
        </div>
    </section>

    <!-- Stories Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Property Owner Story -->
            <div class="mb-16">
                <div class="story-card">
                    <div class="flex items-start space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-key text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Ahmed M.</h3>
                                    <p class="text-gray-600">Property Owner, Al Malqa</p>
                                </div>
                                <span class="category-badge owner-badge">Property Owner</span>
                            </div>
                            <blockquote class="text-xl text-gray-700 mb-6 italic relative z-10">
                                "Partnering with HabibiStay was a game-changer. My Net Operating Income (NOI) increased by an incredible 76% in just 8 months, all without me lifting a finger. Their team handles everything perfectly."
                            </blockquote>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">76%</div>
                                    <div class="text-sm text-gray-600">NOI Increase</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">8</div>
                                    <div class="text-sm text-gray-600">Months</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">100%</div>
                                    <div class="text-sm text-gray-600">Hands-off</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investor Story -->
            <div class="mb-16">
                <div class="story-card">
                    <div class="flex items-start space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Fatima A.</h3>
                                    <p class="text-gray-600">Real Estate Investor</p>
                                </div>
                                <span class="category-badge investor-badge">Investor</span>
                            </div>
                            <blockquote class="text-xl text-gray-700 mb-6 italic relative z-10">
                                "Investing with HabibiStay has been a fantastic experience. I achieved a 15% IRR in my first year, and their transparent reporting keeps me confident in my investment. It's truly hands-off growth."
                            </blockquote>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">15%</div>
                                    <div class="text-sm text-gray-600">First Year IRR</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">Quarterly</div>
                                    <div class="text-sm text-gray-600">Reporting</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">Passive</div>
                                    <div class="text-sm text-gray-600">Income</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Story -->
            <div class="mb-16">
                <div class="story-card">
                    <div class="flex items-start space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-suitcase text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Carlos G.</h3>
                                    <p class="text-gray-600">Business Traveler</p>
                                </div>
                                <span class="category-badge guest-badge">Frequent Guest</span>
                            </div>
                            <blockquote class="text-xl text-gray-700 mb-6 italic relative z-10">
                                "Every time I visit Riyadh for business, I choose HabibiStay. The apartments are consistently excellent—clean, comfortable, and in great locations. Their team provides 4.9★ service, and I've become a repeat guest because they make me feel at home."
                            </blockquote>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-600">4.9★</div>
                                    <div class="text-sm text-gray-600">Service Rating</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">12+</div>
                                    <div class="text-sm text-gray-600">Stays Booked</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">3</div>
                                    <div class="text-sm text-gray-600">Years Loyal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- More Success Stories Grid -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">More Success Stories</h2>
                <p class="text-xl text-gray-600">Join hundreds of satisfied customers who trust HabibiStay</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Quick Story 1 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-home text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Sarah K.</h4>
                            <p class="text-sm text-gray-600">Villa Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"Revenue doubled in 6 months. The dynamic pricing strategy really works!"</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>

                <!-- Quick Story 2 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-briefcase text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Mohammed R.</h4>
                            <p class="text-sm text-gray-600">International Investor</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"Perfect investment solution. Great returns with zero hassle from abroad."</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>

                <!-- Quick Story 3 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-plane text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Lisa W.</h4>
                            <p class="text-sm text-gray-600">Business Traveler</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"Best stays in Riyadh. Always spotless, great locations, and Sara AI is amazing!"</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>

                <!-- Quick Story 4 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-building text-yellow-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Omar T.</h4>
                            <p class="text-sm text-gray-600">Apartment Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"From listing to full occupancy in 2 weeks. Incredible marketing reach!"</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>

                <!-- Quick Story 5 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-heart text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Emma & James</h4>
                            <p class="text-sm text-gray-600">Honeymooners</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"Perfect honeymoon stay! Beautiful apartment and the team surprised us with flowers."</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>

                <!-- Quick Story 6 -->
                <div class="bg-white rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-handshake text-indigo-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">David L.</h4>
                            <p class="text-sm text-gray-600">Buy-to-Let Partner</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"Best decision ever. I own the property, they handle everything else. Pure profit."</p>
                    <div class="mt-4 flex items-center">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Share Your Story CTA -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Share Your HabibiStay Story</h2>
                <p class="text-xl mb-8 opacity-90">
                    Have an amazing experience with HabibiStay? We'd love to hear from you and share your success with our community.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/contact" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                        Share Your Story →
                    </a>
                    <a href="/stays" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-semibold">
                        Start Your Journey
                    </a>
                </div>
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
