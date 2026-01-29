<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About HabibiStay | Local Roots, Global Standards</title>
    <meta name="description" content="Learn about HabibiStay's mission, founders, and values. Local roots with global standards in hospitality and real estate.">
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
        
        .founder-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .founder-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .value-icon {
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
                    <a href="/about" class="text-blue-600 font-semibold">About</a>
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
                    Local Roots,
                    <span class="block">Global Standards</span>
                </h1>
                <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
                    Founded in the heart of Riyadh, HabibiStay merges deep local understanding with cutting-edge technology and international best practices.
                </p>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">Our Story</h2>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Founded in the heart of Riyadh, HabibiStay was born from a passion for genuine Saudi hospitality and a vision to elevate the short-term rental experience. We merge deep local understanding with cutting-edge technology and international best practices to create unforgettable stays for our guests and steady, reliable wealth for our partners. Our commitment is to showcase the best of Riyadh while delivering exceptional value and service.
                </p>
            </div>
        </div>
    </section>

    <!-- Our Founders Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Meet the Visionaries Behind HabibiStay</h2>
                <p class="text-xl text-gray-600">A diverse team of experts dedicated to transforming the hospitality landscape</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Abdullah Mirza -->
                <div class="founder-card text-center">
                    <div class="w-32 h-32 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-laptop-code text-white text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Abdullah Mirza</h3>
                    <p class="text-blue-600 font-semibold mb-4">Tech Visionary</p>
                    <p class="text-gray-600">
                        Driving innovation and ensuring a seamless digital experience for all users. Abdullah brings cutting-edge technology solutions to revolutionize the short-term rental industry in Saudi Arabia.
                    </p>
                    <div class="mt-6 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Vladimir Radchenko -->
                <div class="founder-card text-center">
                    <div class="w-32 h-32 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-white text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Vladimir Radchenko</h3>
                    <p class="text-green-600 font-semibold mb-4">Finance Lead</p>
                    <p class="text-gray-600">
                        Structuring sound investments and financial strategies for sustainable growth. Vladimir ensures robust financial frameworks that deliver consistent returns for all stakeholders.
                    </p>
                    <div class="mt-6 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Anna Miroshenchinko -->
                <div class="founder-card text-center">
                    <div class="w-32 h-32 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-heart text-white text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Anna Miroshenchinko</h3>
                    <p class="text-purple-600 font-semibold mb-4">Experience Curator</p>
                    <p class="text-gray-600">
                        Passionate about creating exceptional guest journeys and maintaining the highest standards of hospitality. Anna ensures every stay exceeds expectations and creates lasting memories.
                    </p>
                    <div class="mt-6 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Guided by Our Core Values</h2>
                <p class="text-xl text-gray-600">The principles that drive everything we do</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Trust</h3>
                    <p class="text-gray-600">Building lasting relationships through transparency, integrity, and reliability. Every interaction is founded on mutual respect and honest communication.</p>
                </div>
                
                <div class="text-center">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Excellence</h3>
                    <p class="text-gray-600">Striving for the highest standards in everything we do, from property care to guest service. We never settle for good enough when exceptional is possible.</p>
                </div>
                
                <div class="text-center">
                    <div class="value-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Shared Growth</h3>
                    <p class="text-gray-600">Creating win-win scenarios where our guests, owners, investors, and community thrive together. Success is meaningful only when it's shared.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 text-white">
                <div class="text-center lg:text-left">
                    <h3 class="text-3xl font-bold mb-6">Our Mission</h3>
                    <p class="text-xl opacity-90">
                        To transform Riyadh's hospitality landscape by creating exceptional experiences for travelers while generating sustainable wealth for property owners and investors through innovative technology and genuine Saudi hospitality.
                    </p>
                </div>
                <div class="text-center lg:text-left">
                    <h3 class="text-3xl font-bold mb-6">Our Vision</h3>
                    <p class="text-xl opacity-90">
                        To become the leading platform that showcases Saudi Arabia's world-class hospitality to global travelers while creating a thriving ecosystem of profitable real estate investments that benefits our entire community.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Impact</h2>
                <p class="text-xl text-gray-600">The numbers that tell our story</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-600 mb-2">50+</div>
                    <div class="text-gray-600">Premium Properties</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">1000+</div>
                    <div class="text-gray-600">Happy Guests</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-purple-600 mb-2">95%</div>
                    <div class="text-gray-600">Satisfaction Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-600 mb-2">24/7</div>
                    <div class="text-gray-600">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Us CTA -->
    <section class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Join the HabibiStay Journey</h2>
            <p class="text-xl text-gray-600 mb-8">
                Whether you're a traveler seeking exceptional stays, a property owner looking to maximize returns, or an investor interested in Riyadh's growing market, we invite you to be part of our story.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/stays" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    Book Your Stay
                </a>
                <a href="/host" class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    List Your Property
                </a>
                <a href="/invest" class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    Explore Investments
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
