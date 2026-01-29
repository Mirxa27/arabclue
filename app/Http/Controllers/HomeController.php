<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Review;
use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Home Controller - Public-facing page management
 * Implements caching strategies for optimal performance
 */
class HomeController extends Controller
{
    /**
     * Display the application homepage
     * Implements fragment caching for dynamic content sections
     */
    public function index()
    {
        // Cache featured properties for 1 hour
        $featuredProperties = Cache::remember('home_featured_properties', 3600, function () {
            return Property::with(['primaryImage', 'owner'])
                ->active()
                ->featured()
                ->withAvg('reviews', 'rating')
                ->limit(6)
                ->get();
        });

        // Static content from PRD
        $heroContent = (object) [
            'title' => 'Exceptional Stays. Exceptional Returns.',
            'subtitle' => 'Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay.'
        ];

        $whySection = (object) [
            'title' => 'Why HabibiStay?',
            'subtitle' => 'Discover the HabibiStay difference, tailored for everyone:'
        ];

        $testimonials = [
            (object) [
                'content' => 'HabibiStay doubled my rental income in six months. Their professionalism and market knowledge are unmatched.',
                'name' => 'Ahmed',
                'role' => 'Riyadh',
                'avatar' => '/assets/images/avatars/ahmed.jpg' // Placeholder
            ],
            (object) [
                'content' => 'Partnering with HabibiStay was a game-changer. My Net Operating Income (NOI) increased by an incredible 76% in just 8 months, all without me lifting a finger. Their team handles everything perfectly.',
                'name' => 'Ahmed M.',
                'role' => 'Property Owner',
                'avatar' => '/assets/images/avatars/ahmed-m.jpg' // Placeholder
            ],
            (object) [
                'content' => 'Investing with HabibiStay has been a fantastic experience. I achieved a 15% IRR in my first year, and their transparent reporting keeps me confident in my investment. It\'s truly hands-off growth.',
                'name' => 'Fatima A.',
                'role' => 'Investor',
                'avatar' => '/assets/images/avatars/fatima-a.jpg' // Placeholder
            ],
            (object) [
                'content' => 'Every time I visit Riyadh for business, I choose HabibiStay. The apartments are consistently excellent—clean, comfortable, and in great locations. Their team provides 4.9★ service, and I\'ve become a repeat guest because they make me feel at home.',
                'name' => 'Carlos G.',
                'role' => 'Frequent Guest',
                'avatar' => '/assets/images/avatars/carlos-g.jpg' // Placeholder
            ]
        ];
        
        $stats = $this->getStatistics();
        
        return view('pages.home', compact('featuredProperties', 'testimonials', 'heroContent', 'whySection', 'stats'));
    }
    
    /**
     * Display How It Works page
     */
    public function howItWorks()
    {
        $content = ContentBlock::where('identifier', 'how_it_works')->first();
        return view('how-it-works', compact('content'));
    }
    
    /**
     * Display About page
     */
    public function about()
    {
        $content = ContentBlock::where('identifier', 'about_us')->first();
        $team = $this->getTeamMembers();
        return view('pages.about', compact('content', 'team'));
    }
    
    /**
     * Display Contact page
     */
    public function contact()
    {
        return view('contact');
    }
    
    /**
     * Display Stories page
     */
    public function stories()
    {
        return view('pages.stories');
    }
    
    /**
     * Display Blog page
     */
    public function blog()
    {
        return view('pages.blog');
    }
    
    /**
     * Display Terms page
     */
    public function terms()
    {
        return view('legal.terms');
    }
    
    /**
     * Display Privacy page
     */
    public function privacy()
    {
        return view('legal.privacy');
    }
    
    /**
     * Display Support page
     */
    public function support()
    {
        return view('support');
    }
    
    /**
     * Display Host Landing page
     */
    public function hostLanding()
    {
        return view('pages.owners');
    }
    
    /**
     * Display Invest Landing page
     */
    public function investLanding()
    {
        return view('pages.invest');
    }
    
    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'interested_in' => 'required|in:booking,listing,investing,general'
        ]);
        
        // Store inquiry and send notification
        \App\Models\ContactInquiry::create($validated);
        
        // Dispatch email notification
        dispatch(new \App\Jobs\SendContactNotification($validated));
        
        return back()->with('success', 'Thank you for contacting us. We\'ll respond within 24 hours.');
    }
    
    /**
     * Get platform statistics
     */
    protected function getStatistics(): array
    {
        return Cache::remember('platform_stats', 3600, function () {
            return [
                'total_properties' => Property::active()->count(),
                'total_cities' => Property::active()->distinct('city')->count(),
                'total_bookings' => \App\Models\Booking::completed()->count(),
                'average_rating' => Property::active()->avg('overall_rating') ?? 4.8
            ];
        });
    }
    
    /**
     * Get team members
     */
    protected function getTeamMembers(): array
    {
        return [
            [
                'name' => 'Abdullah Mirza',
                'role' => 'Tech Visionary',
                'bio' => 'Driving innovation and ensuring a seamless digital experience for all users.',
                'image' => '/assets/images/team/abdullah.jpg'
            ],
            [
                'name' => 'Vladimir Radchenko',
                'role' => 'Finance Lead',
                'bio' => 'Structuring sound investments and financial strategies for sustainable growth.',
                'image' => '/assets/images/team/vladimir.jpg'
            ],
            [
                'name' => 'Anna Miroshenchinko',
                'role' => 'Experience Curator',
                'bio' => 'Passionate about creating exceptional guest journeys and maintaining the highest standards of hospitality.',
                'image' => '/assets/images/team/anna.jpg'
            ]
        ];
    }
}
