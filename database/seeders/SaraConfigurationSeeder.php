<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaraConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $saraConfig = [
            'welcome_message' => "Hi there! I'm Sara, your HabibiStay assistant. How can I help you find the perfect stay today?",
            'enable_voice' => true,
            'enable_buttons' => true,
            'model' => 'gpt-4-turbo-preview',
            'temperature' => 0.7,
            'max_tokens' => 800,
            'voice_id' => 'EXAVITQu4vr4xnSDxMaL',
            'voice_provider' => 'elevenlabs',
            'suggested_actions' => [
                ['text' => 'Find a place', 'action' => 'I need a place to stay'],
                ['text' => 'Learn about HabibiStay', 'action' => 'Tell me about HabibiStay'],
                ['text' => 'Contact support', 'action' => 'I need help']
            ],
            'fallback_message' => "I'm sorry, I didn't understand that. How can I help you with finding a property or making a booking?"
        ];

        DB::table('configurations')->updateOrInsert(
            ['key' => 'sara'],
            [
                'value' => json_encode($saraConfig),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
