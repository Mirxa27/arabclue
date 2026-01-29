@extends('layouts.app')

@section('title', 'Privacy Policy - HabibiStay')

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
            
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-600 mb-6">Last updated: {{ date('F j, Y') }}</p>
                
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">1. Information We Collect</h2>
                <p class="text-gray-700 mb-4">
                    We collect information you provide directly to us, such as when you create an account, make a booking, or contact us for support.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">2. How We Use Your Information</h2>
                <p class="text-gray-700 mb-4">
                    We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">3. Information Sharing</h2>
                <p class="text-gray-700 mb-4">
                    We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">4. Data Security</h2>
                <p class="text-gray-700 mb-4">
                    We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">5. Cookies</h2>
                <p class="text-gray-700 mb-4">
                    We use cookies to enhance your experience on our website. You can choose to disable cookies through your browser settings.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">6. Your Rights</h2>
                <p class="text-gray-700 mb-4">
                    You have the right to access, update, or delete your personal information. You may also opt out of certain communications from us.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">7. Changes to This Policy</h2>
                <p class="text-gray-700 mb-4">
                    We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">8. Contact Us</h2>
                <p class="text-gray-700 mb-4">
                    If you have any questions about this privacy policy, please contact us at privacy@habibistay.com.
                </p>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <p class="text-gray-600">
                        For more information about our privacy practices, please contact us at 
                        <a href="mailto:privacy@habibistay.com" class="text-blue-600 hover:text-blue-800">privacy@habibistay.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
