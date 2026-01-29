@extends('layouts.app')

@section('title', 'Terms of Service - HabibiStay')

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">Terms of Service</h1>
            
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-600 mb-6">Last updated: {{ date('F j, Y') }}</p>
                
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">1. Acceptance of Terms</h2>
                <p class="text-gray-700 mb-4">
                    By accessing and using HabibiStay, you accept and agree to be bound by the terms and provision of this agreement.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">2. Use License</h2>
                <p class="text-gray-700 mb-4">
                    Permission is granted to temporarily download one copy of the materials on HabibiStay's website for personal, non-commercial transitory viewing only.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">3. Disclaimer</h2>
                <p class="text-gray-700 mb-4">
                    The materials on HabibiStay's website are provided on an 'as is' basis. HabibiStay makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">4. Limitations</h2>
                <p class="text-gray-700 mb-4">
                    In no event shall HabibiStay or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on HabibiStay's website, even if HabibiStay or a HabibiStay authorized representative has been notified orally or in writing of the possibility of such damage.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">5. Accuracy of Materials</h2>
                <p class="text-gray-700 mb-4">
                    The materials appearing on HabibiStay's website could include technical, typographical, or photographic errors. HabibiStay does not warrant that any of the materials on its website are accurate, complete, or current.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">6. Links</h2>
                <p class="text-gray-700 mb-4">
                    HabibiStay has not reviewed all of the sites linked to our website and is not responsible for the contents of any such linked site.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">7. Modifications</h2>
                <p class="text-gray-700 mb-4">
                    HabibiStay may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">8. Governing Law</h2>
                <p class="text-gray-700 mb-4">
                    These terms and conditions are governed by and construed in accordance with the laws of Saudi Arabia and you irrevocably submit to the exclusive jurisdiction of the courts in that state or location.
                </p>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <p class="text-gray-600">
                        If you have any questions about these Terms of Service, please contact us at 
                        <a href="mailto:support@habibistay.com" class="text-blue-600 hover:text-blue-800">support@habibistay.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
