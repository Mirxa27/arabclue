@extends('layouts.app')

@section('title', 'Support - HabibiStay')

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Support Center</h1>
            <p class="text-xl text-gray-600">Find answers to your questions or get in touch with our support team.</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-robot text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Chat with Sara AI</h3>
                <p class="text-gray-600 mb-4">Get instant answers to common questions with our AI assistant.</p>
                <a href="/sara" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Start Chat
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fab fa-whatsapp text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">WhatsApp Support</h3>
                <p class="text-gray-600 mb-4">Message us directly on WhatsApp for quick assistance.</p>
                <a href="https://wa.me/966550800669" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    Message Us
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Email Support</h3>
                <p class="text-gray-600 mb-4">Send us an email and we'll get back to you within 24 hours.</p>
                <a href="mailto:support@habibistay.com" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                    Send Email
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Frequently Asked Questions</h2>
            
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I make a booking?</h3>
                    <p class="text-gray-600">You can make a booking by searching for properties on our homepage, selecting your desired property, and following the booking process. You'll need to create an account and provide payment information.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What is the cancellation policy?</h3>
                    <p class="text-gray-600">Cancellation policies vary by property. You can find the specific cancellation policy for each property on its listing page. Most properties offer flexible, moderate, or strict cancellation policies.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I become a host?</h3>
                    <p class="text-gray-600">To become a host, click on the "List Property" button on our homepage or visit the host section. You'll need to provide property details, photos, and complete the verification process.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How do payments work?</h3>
                    <p class="text-gray-600">We accept various payment methods including credit cards, debit cards, and digital wallets. Payments are processed securely through our platform, and hosts receive payouts after successful check-ins.</p>
                </div>

                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What if I have issues during my stay?</h3>
                    <p class="text-gray-600">If you encounter any issues during your stay, contact us immediately through our 24/7 support channels. We're here to help resolve any problems and ensure you have a great experience.</p>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="mt-12 bg-blue-600 rounded-lg p-8 text-white text-center">
            <h2 class="text-2xl font-bold mb-4">Still Need Help?</h2>
            <p class="text-blue-100 mb-6">Our support team is available 24/7 to assist you with any questions or concerns.</p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-8">
                <div class="flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    <span>+966 550 800 669</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope mr-2"></i>
                    <span>support@habibistay.com</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
