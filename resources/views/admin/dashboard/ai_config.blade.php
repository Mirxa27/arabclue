@extends('layouts.admin')

@section('title', 'Sara AI Configuration')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Sara AI Configuration
    </h2>

    <!-- AI Model Settings Card -->
    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.sara.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">AI Model Selection</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select AI Provider
                    </label>
                    <select name="ai_provider" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="openai" {{ $settings->ai_provider == 'openai' ? 'selected' : '' }}>OpenAI</option>
                        <option value="azure" {{ $settings->ai_provider == 'azure' ? 'selected' : '' }}>Azure OpenAI</option>
                        <option value="anthropic" {{ $settings->ai_provider == 'anthropic' ? 'selected' : '' }}>Anthropic Claude</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Model Name
                    </label>
                    <select name="model_name" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="gpt-4" {{ $settings->model_name == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                        <option value="gpt-4-turbo" {{ $settings->model_name == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                        <option value="gpt-3.5-turbo" {{ $settings->model_name == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                        <option value="claude-3-opus" {{ $settings->model_name == 'claude-3-opus' ? 'selected' : '' }}>Claude 3 Opus</option>
                        <option value="claude-3-sonnet" {{ $settings->model_name == 'claude-3-sonnet' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                        <option value="claude-3-haiku" {{ $settings->model_name == 'claude-3-haiku' ? 'selected' : '' }}>Claude 3 Haiku</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        API Key
                    </label>
                    <input type="password" name="api_key" value="{{ $settings->api_key }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        API Endpoint (for Azure)
                    </label>
                    <input type="text" name="api_endpoint" value="{{ $settings->api_endpoint }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Sara Behavior Settings -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Sara Behavior Configuration</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sara Personality
                    </label>
                    <textarea name="sara_personality" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $settings->sara_personality }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Describe Sara's personality and how she should interact with users.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Initial Greeting Message
                    </label>
                    <textarea name="initial_greeting" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $settings->initial_greeting }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Featured Properties Selection
                    </label>
                    <select name="featured_properties_method" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="manual" {{ $settings->featured_properties_method == 'manual' ? 'selected' : '' }}>Manual Selection</option>
                        <option value="automatic" {{ $settings->featured_properties_method == 'automatic' ? 'selected' : '' }}>Automatic (Based on Ratings)</option>
                        <option value="ai_recommended" {{ $settings->featured_properties_method == 'ai_recommended' ? 'selected' : '' }}>AI Recommended</option>
                    </select>
                </div>

                @if($settings->featured_properties_method == 'manual')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Featured Properties
                    </label>
                    <select name="featured_properties[]" multiple class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" size="5">
                        @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ in_array($property->id, $featuredPropertyIds) ? 'selected' : '' }}>
                            {{ $property->name }} - {{ $property->location }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- UI Integration Settings -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">UI Integration</h3>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="enable_voice_input" id="enable_voice_input" {{ $settings->enable_voice_input ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_voice_input" class="ml-2 block text-sm text-gray-700">
                        Enable Voice Input
                    </label>
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="enable_button_interface" id="enable_button_interface" {{ $settings->enable_button_interface ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_button_interface" class="ml-2 block text-sm text-gray-700">
                        Enable Button Interface
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sara Chat Interface Style
                    </label>
                    <select name="chat_interface_style" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="floating" {{ $settings->chat_interface_style == 'floating' ? 'selected' : '' }}>Floating Bubble</option>
                        <option value="embedded" {{ $settings->chat_interface_style == 'embedded' ? 'selected' : '' }}>Embedded Chat</option>
                        <option value="fullscreen" {{ $settings->chat_interface_style == 'fullscreen' ? 'selected' : '' }}>Fullscreen on Mobile</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Primary Brand Color
                    </label>
                    <input type="color" name="primary_color" value="{{ $settings->primary_color }}" class="h-10 w-32">
                    <p class="mt-1 text-sm text-gray-500">Default: #2957c3</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
