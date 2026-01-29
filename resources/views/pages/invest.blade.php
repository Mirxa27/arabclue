<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Opportunities | HabibiStay</title>
    <meta name="description" content="Grow your wealth in Riyadh's booming real estate market with HabibiStay's secure investment opportunities.">
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
        
        .investment-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .investment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--brand-blue);
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
                    <a href="/invest" class="text-blue-600 font-semibold">Invest</a>
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
                    Grow Your Wealth in
                    <span class="block">Riyadh's Booming Market.</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    Secure, hands‑free real estate investment opportunities for strong returns, backed by HabibiStay's expertise.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                    <a href="#investment-options" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                        Request Investor Deck →
                    </a>
                    <a href="/contact" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-semibold">
                        Schedule Call
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Investment Options Section -->
    <section class="py-16 bg-white" id="investment-options">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Choose Your Path to Real Estate Returns</h2>
                <p class="text-xl text-gray-600">Multiple investment options tailored to your goals and capital requirements</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Capital Investor -->
                <div class="investment-card">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Capital Investor</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Pool your capital with other discerning investors into our existing, high-performing property portfolio. Enjoy passive income through regular quarterly dividends and benefit from professional asset management.</p>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Minimum investment: $25,000</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Quarterly dividend payments</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Professional asset management</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Target IRR: 15-18%</span>
                        </div>
                    </div>
                    <button class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        Learn More
                    </button>
                </div>

                <!-- International Investor -->
                <div class="investment-card">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-globe text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">International Investor</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Looking to own property in Riyadh? We leverage our local expertise to source, acquire, and fully manage prime real estate on your behalf, delivering turnkey investment solutions for international clients.</p>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Property sourcing & acquisition</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Full property management</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Legal & compliance support</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Minimum: $200,000</span>
                        </div>
                    </div>
                    <button class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        Explore Options
                    </button>
                </div>

                <!-- Buy-to-Let Partnership -->
                <div class="investment-card">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-handshake text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Buy-to-Let Partnership</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Acquire a property in Riyadh, and let HabibiStay operate it. We manage the entire rental process, ensuring it's optimized for maximum returns while you retain full ownership.</p>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>You own the property</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>We manage operations</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Optimized rental yields</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Monthly payouts</span>
                        </div>
                    </div>
                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                        Get Started
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Invest with HabibiStay Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Your Trusted Partner in Real Estate Investment</h2>
                <p class="text-xl text-gray-600">Proven expertise and transparent operations for your peace of mind</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Proven Track Record</h3>
                    <p class="text-gray-600">We have a history of delivering strong, consistent returns for our investment partners through strategic property selection and management.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Data-Driven Underwriting</h3>
                    <p class="text-gray-600">Our investment decisions are backed by rigorous market analysis, financial modeling, and comprehensive due diligence to minimize risk and maximize profitability.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Transparent Quarterly Reporting</h3>
                    <p class="text-gray-600">Stay informed with clear, detailed quarterly reports on your investment's performance, financials, and market outlook.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Performance Metrics -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Investment Performance</h2>
                <p class="text-xl text-gray-600">Consistent returns across our portfolio</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center bg-blue-50 rounded-2xl p-6">
                    <div class="text-3xl font-bold text-blue-600 mb-2">17%</div>
                    <div class="text-gray-600">Average Annual ROI</div>
                </div>
                <div class="text-center bg-green-50 rounded-2xl p-6">
                    <div class="text-3xl font-bold text-green-600 mb-2">76%</div>
                    <div class="text-gray-600">NOI Increase</div>
                </div>
                <div class="text-center bg-purple-50 rounded-2xl p-6">
                    <div class="text-3xl font-bold text-purple-600 mb-2">95%</div>
                    <div class="text-gray-600">Occupancy Rate</div>
                </div>
                <div class="text-center bg-yellow-50 rounded-2xl p-6">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">$50M+</div>
                    <div class="text-gray-600">Assets Under Management</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Investor Deck CTA -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Investing?</h2>
                <p class="text-xl mb-8 opacity-90">Request our comprehensive investor deck with detailed market analysis, portfolio performance, and investment opportunities.</p>
                
                <div class="bg-white rounded-2xl p-8 max-w-2xl mx-auto">
                    <form class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="text" placeholder="Full Name" required
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
                                    <option value="">Investment Interest</option>
                                    <option value="capital">Capital Investor</option>
                                    <option value="international">International Investor</option>
                                    <option value="buy-to-let">Buy-to-Let Partnership</option>
                                    <option value="multiple">Multiple Options</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <select required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Investment Amount Range</option>
                                <option value="25k-100k">$25,000 - $100,000</option>
                                <option value="100k-500k">$100,000 - $500,000</option>
                                <option value="500k-1m">$500,000 - $1,000,000</option>
                                <option value="1m+">$1,000,000+</option>
                            </select>
                        </div>
                        <div>
                            <textarea placeholder="Additional notes or questions (optional)" rows="3"
                                      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            Request Investor Deck →
                        </button>
                    </form>
                    <p class="text-sm text-gray-500 mt-4">Our investment team will contact you within 24 hours with your personalized investor package.</p>
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
