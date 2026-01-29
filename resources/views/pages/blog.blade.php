<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabibiStay Blog | Insights & Opportunities</title>
    <meta name="description" content="Stay updated with the latest news, tips, and trends in Riyadh's property market and travel scene.">
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
        
        .blog-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }
        
        .featured-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .category-tag {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .travel-tag {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .investment-tag {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .property-tag {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .market-tag {
            background: #f3e5f5;
            color: #7b1fa2;
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
                    <a href="/stories" class="text-gray-700 hover:text-blue-600 transition-colors">Stories</a>
                    <a href="/blog" class="text-blue-600 font-semibold">Blog</a>
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
                    Insights & Opportunities:
                    <span class="block">The HabibiStay Blog</span>
                </h1>
                <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
                    Stay updated with the latest news, tips, and trends in Riyadh's property market and travel scene.
                </p>
            </div>
        </div>
    </section>

    <!-- Featured Article -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="featured-card">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <div class="relative">
                        <img src="/images/blog/riyadh-skyline.jpg" alt="Riyadh 2025" 
                             class="w-full h-64 lg:h-full object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag travel-tag">Travel</span>
                        </div>
                    </div>
                    <div class="p-8 lg:p-12 flex flex-col justify-center">
                        <div class="text-sm text-gray-500 mb-2">Featured Article • December 15, 2024</div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Top 5 Reasons to Visit Riyadh in 2025
                        </h2>
                        <p class="text-xl text-gray-600 mb-6">
                            From groundbreaking giga-projects to vibrant cultural festivals, discover why Riyadh is the must-visit destination next year. Explore the transformation of Saudi Arabia's capital into a global hub.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Anna Miroshenchinko</div>
                                    <div class="text-sm text-gray-500">Experience Curator</div>
                                </div>
                            </div>
                            <a href="#" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                Read More →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Categories -->
    <section class="py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4">
                <button class="category-tag travel-tag hover:opacity-80 transition-opacity cursor-pointer">Travel</button>
                <button class="category-tag investment-tag hover:opacity-80 transition-opacity cursor-pointer">Investment</button>
                <button class="category-tag property-tag hover:opacity-80 transition-opacity cursor-pointer">Property</button>
                <button class="category-tag market-tag hover:opacity-80 transition-opacity cursor-pointer">Market Insights</button>
            </div>
        </div>
    </section>

    <!-- Blog Posts Grid -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Blog Post 1 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/property-preparation.jpg" alt="Property Preparation" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag property-tag">Property</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 12, 2024 • 8 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            How to Make Your Property Guest-Ready for Maximum Appeal
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Unlock higher bookings and glowing reviews. Our expert tips on preparing your property to delight every guest and maximize your rental income.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Property Team</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 2 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/riyadh-investment.jpg" alt="Riyadh Investment" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag investment-tag">Investment</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 10, 2024 • 12 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            Riyadh: The Next Big Investment Hub You Shouldn't Ignore
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Driven by Vision 2030, Riyadh's real estate market offers unprecedented opportunities. Here's why savvy investors are taking notice.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Vladimir Radchenko</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 3 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/ai-hospitality.jpg" alt="AI in Hospitality" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag market-tag">Tech</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 8, 2024 • 6 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            How AI is Revolutionizing the Hospitality Experience
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Meet Sara, our AI assistant, and discover how artificial intelligence is transforming guest experiences and property management.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Abdullah Mirza</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 4 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/saudi-culture.jpg" alt="Saudi Culture" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag travel-tag">Travel</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 5, 2024 • 10 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            A First-Timer's Guide to Saudi Arabian Culture and Etiquette
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Essential insights for international visitors to feel comfortable and respectful during their stay in the Kingdom.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Guest Relations</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 5 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/rental-yields.jpg" alt="Rental Yields" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag investment-tag">Investment</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 3, 2024 • 9 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            Understanding Rental Yields in Riyadh's Prime Districts
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Compare rental yields across Riyadh's most sought-after neighborhoods and make informed investment decisions.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Market Research</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 6 -->
                <article class="blog-card">
                    <div class="relative">
                        <img src="/images/blog/vision-2030.jpg" alt="Vision 2030" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="category-tag market-tag">Market</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">December 1, 2024 • 15 min read</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            Vision 2030's Impact on Saudi Arabia's Tourism Sector
                        </h3>
                        <p class="text-gray-600 mb-4">
                            How the Kingdom's ambitious transformation plan is creating massive opportunities in hospitality and real estate.
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Strategy Team</span>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Read More →</a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-12">
                <button class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    Load More Posts
                </button>
            </div>
        </div>
    </section>

    <!-- Newsletter Signup -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Stay Informed</h2>
                <p class="text-xl mb-8 opacity-90">
                    Get the latest insights, market updates, and exclusive content delivered to your inbox monthly.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-lg mx-auto">
                    <input type="email" placeholder="Enter your email address" 
                           class="flex-1 px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white focus:ring-opacity-50">
                    <button class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                        Subscribe
                    </button>
                </div>
                <p class="text-sm opacity-75 mt-4">Unsubscribe anytime. We respect your privacy.</p>
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
