<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtensionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table to ensure a clean slate (optional, but matches the SQL DROP)
        // DB::table('extensions')->truncate(); 
        // Better to use upsert or check existence to avoid FK issues if they exist.
        // But the SQL was a full replace. Let's stick to updateOrInsert.

        $extensions = [
            ['id' => 1, 'version' => '1.1', 'slug' => 'ai-creative-suite', 'installed' => 1, 'is_theme' => 0],
            ['id' => 2, 'version' => '1.1', 'slug' => 'url-to-video-ad', 'installed' => 1, 'is_theme' => 0],
            ['id' => 3, 'version' => '1.0', 'slug' => 'viral-clips', 'installed' => 1, 'is_theme' => 0],
            ['id' => 4, 'version' => '1.0', 'slug' => 'influencer-avatars', 'installed' => 1, 'is_theme' => 0],
            ['id' => 5, 'version' => '2.3', 'slug' => 'marketing-bot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 6, 'version' => '2.1', 'slug' => 'chatbot-voice', 'installed' => 1, 'is_theme' => 0],
            ['id' => 7, 'version' => '1.4', 'slug' => 'live-customizer', 'installed' => 1, 'is_theme' => 0],
            ['id' => 8, 'version' => '5.1', 'slug' => 'chatbot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 9, 'version' => '4.2', 'slug' => 'social-media-extension', 'installed' => 1, 'is_theme' => 0],
            ['id' => 10, 'version' => '2.7', 'slug' => 'ai-chat-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 11, 'version' => '2.4', 'slug' => 'menu', 'installed' => 1, 'is_theme' => 0],
            ['id' => 12, 'version' => '2.2', 'slug' => 'chatbot-agent', 'installed' => 1, 'is_theme' => 0],
            ['id' => 13, 'version' => '1.1', 'slug' => 'mega-menu', 'installed' => 1, 'is_theme' => 0],
            ['id' => 14, 'version' => '2.3', 'slug' => 'advanced-image', 'installed' => 1, 'is_theme' => 0],
            ['id' => 15, 'version' => '1.2', 'slug' => 'whatsapp', 'installed' => 1, 'is_theme' => 0],
            ['id' => 16, 'version' => '1.4', 'slug' => 'onboarding-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 17, 'version' => '1.7', 'slug' => 'openai-realtime-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 18, 'version' => '1.2', 'slug' => 'ai-avatar-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 19, 'version' => '1.0', 'slug' => 'azure-openai', 'installed' => 1, 'is_theme' => 0],
            ['id' => 20, 'version' => '1.3', 'slug' => 'telegram', 'installed' => 1, 'is_theme' => 0],
            ['id' => 21, 'version' => '2.3', 'slug' => 'ai-fall-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 22, 'version' => '1.6', 'slug' => 'ai-realtime-image', 'installed' => 1, 'is_theme' => 0],
            ['id' => 23, 'version' => '2.6', 'slug' => 'chat-share', 'installed' => 1, 'is_theme' => 0],
            ['id' => 24, 'version' => '2.1', 'slug' => 'focus-mode', 'installed' => 1, 'is_theme' => 0],
            ['id' => 25, 'version' => '2.0', 'slug' => 'introductions', 'installed' => 1, 'is_theme' => 0],
            ['id' => 26, 'version' => '2.3', 'slug' => 'flux-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 27, 'version' => '1.0', 'slug' => 'public-announcements', 'installed' => 1, 'is_theme' => 0],
            ['id' => 28, 'version' => '2.1', 'slug' => 'voice-isolator', 'installed' => 1, 'is_theme' => 0],
            ['id' => 29, 'version' => '2.0', 'slug' => 'hubspot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 30, 'version' => '2.0', 'slug' => 'mailchimp-newsletter', 'installed' => 1, 'is_theme' => 0],
            ['id' => 31, 'version' => '2.3', 'slug' => 'ai-product-shot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 32, 'version' => '2.2', 'slug' => 'ai-avatar', 'installed' => 1, 'is_theme' => 0],
            ['id' => 33, 'version' => '2.0', 'slug' => 'maintenance', 'installed' => 1, 'is_theme' => 0],
            ['id' => 34, 'version' => '2.0', 'slug' => 'ai-writer-templates', 'installed' => 1, 'is_theme' => 0],
            ['id' => 35, 'version' => '3.4', 'slug' => 'seo-tool', 'installed' => 1, 'is_theme' => 0],
            ['id' => 36, 'version' => '2.1', 'slug' => 'azure-tts', 'installed' => 1, 'is_theme' => 0],
            ['id' => 37, 'version' => '3.2', 'slug' => 'cryptomus', 'installed' => 1, 'is_theme' => 0],
            ['id' => 38, 'version' => '4.6', 'slug' => 'ai-social-media', 'installed' => 1, 'is_theme' => 0],
            ['id' => 39, 'version' => '3.0', 'slug' => 'wordpress', 'installed' => 1, 'is_theme' => 0],
            ['id' => 40, 'version' => '3.2', 'slug' => 'cloudflare-r2', 'installed' => 1, 'is_theme' => 0],
            ['id' => 41, 'version' => '3.2', 'slug' => 'chat-setting', 'installed' => 1, 'is_theme' => 0],
            ['id' => 42, 'version' => '2.8', 'slug' => 'webchat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 43, 'version' => '2.1', 'slug' => 'plagiarism', 'installed' => 1, 'is_theme' => 0],
            ['id' => 44, 'version' => '2.0', 'slug' => 'newsletter', 'installed' => 1, 'is_theme' => 0],
            ['id' => 45, 'version' => '1.0', 'slug' => 'perplexity', 'installed' => 1, 'is_theme' => 0],
            ['id' => 46, 'version' => '1.4', 'slug' => 'checkout-registration', 'installed' => 1, 'is_theme' => 0],
            ['id' => 47, 'version' => '1.4', 'slug' => 'ai-video-to-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 48, 'version' => '2.4', 'slug' => 'midjourney', 'installed' => 1, 'is_theme' => 0],
            ['id' => 49, 'version' => '1.3', 'slug' => 'ai-music', 'installed' => 1, 'is_theme' => 0],
            ['id' => 50, 'version' => '1.1', 'slug' => 'open-router', 'installed' => 1, 'is_theme' => 0],
            ['id' => 51, 'version' => '1.0', 'slug' => 'only-show-mobile', 'installed' => 1, 'is_theme' => 0],
            ['id' => 52, 'version' => '1.0', 'slug' => 'xero', 'installed' => 1, 'is_theme' => 0],
            ['id' => 53, 'version' => '1.2', 'slug' => 'migration', 'installed' => 1, 'is_theme' => 0],
            ['id' => 54, 'version' => '5.6', 'slug' => 'bolt', 'installed' => 1, 'is_theme' => 1],
            ['id' => 55, 'version' => '4.8', 'slug' => 'modern', 'installed' => 1, 'is_theme' => 1],
            ['id' => 56, 'version' => '1.1', 'slug' => 'social-media-frontend', 'installed' => 1, 'is_theme' => 1],
            ['id' => 57, 'version' => '1.8', 'slug' => 'social-media-dashboard', 'installed' => 1, 'is_theme' => 1],
            ['id' => 58, 'version' => '4.8', 'slug' => 'dark', 'installed' => 1, 'is_theme' => 1],
            ['id' => 59, 'version' => '5.1', 'slug' => 'classic', 'installed' => 1, 'is_theme' => 1],
            ['id' => 60, 'version' => '4.6', 'slug' => 'creative', 'installed' => 1, 'is_theme' => 1],
            ['id' => 61, 'version' => '5.1', 'slug' => 'sleek', 'installed' => 1, 'is_theme' => 1],
            ['id' => 62, 'version' => '1', 'slug' => 'default', 'installed' => 0, 'is_theme' => 1],
            ['id' => 63, 'version' => '1.0', 'slug' => 'mobile', 'installed' => 0, 'is_theme' => 0],
            ['id' => 64, 'version' => '1.0', 'slug' => 'eleven-labs-voice-chat', 'installed' => 0, 'is_theme' => 0],
            ['id' => 65, 'version' => '1.5', 'slug' => 'creative-suite', 'installed' => 1, 'is_theme' => 0],
            ['id' => 66, 'version' => '1.5', 'slug' => 'url-to-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 67, 'version' => '1.3', 'slug' => 'ai-viral-clips', 'installed' => 1, 'is_theme' => 0],
            ['id' => 68, 'version' => '1.4', 'slug' => 'influencer-avatar', 'installed' => 1, 'is_theme' => 0],
            ['id' => 69, 'version' => '5.2', 'slug' => 'social-media', 'installed' => 1, 'is_theme' => 0],
            ['id' => 70, 'version' => '1.4', 'slug' => 'chatbot-whatsapp', 'installed' => 1, 'is_theme' => 0],
            ['id' => 71, 'version' => '2.3', 'slug' => 'ai-persona', 'installed' => 1, 'is_theme' => 0],
            ['id' => 72, 'version' => '1.2', 'slug' => 'elevenlabs-voice-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 73, 'version' => '1.4', 'slug' => 'chatbot-telegram', 'installed' => 1, 'is_theme' => 0],
            ['id' => 74, 'version' => '3.0', 'slug' => 'ai-video-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 75, 'version' => '1.3', 'slug' => 'announcement', 'installed' => 1, 'is_theme' => 0],
            ['id' => 76, 'version' => '1.8', 'slug' => 'marketing-bot-dashboard', 'installed' => 1, 'is_theme' => 1],
            ['id' => 77, 'version' => '1.2', 'slug' => 'marketing-bot-frontend', 'installed' => 1, 'is_theme' => 1],
            ['id' => 78, 'version' => '1.6', 'slug' => 'canvas', 'installed' => 1, 'is_theme' => 0],
            ['id' => 79, 'version' => '1.1', 'slug' => 'content-manager', 'installed' => 1, 'is_theme' => 0],
            ['id' => 80, 'version' => '1.0', 'slug' => 'ai-replica', 'installed' => 1, 'is_theme' => 0],
            ['id' => 81, 'version' => '1.1', 'slug' => 'chatbot-messenger', 'installed' => 1, 'is_theme' => 0],
            ['id' => 82, 'version' => '1.0', 'slug' => 'speechify-tts', 'installed' => 0, 'is_theme' => 0],
            ['id' => 83, 'version' => '1.3', 'slug' => 'discount-manager', 'installed' => 1, 'is_theme' => 0],
            ['id' => 84, 'version' => '1.0', 'slug' => 'footer-menu', 'installed' => 0, 'is_theme' => 0],
            ['id' => 85, 'version' => '1.2', 'slug' => 'chat-pro-temp-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 86, 'version' => '1.2', 'slug' => 'nano-banana', 'installed' => 1, 'is_theme' => 0],
            ['id' => 87, 'version' => '1.0', 'slug' => '10-must-have-apps-for-ai-video-generation-1756899408', 'installed' => 0, 'is_theme' => 0],
            ['id' => 88, 'version' => '1.2', 'slug' => 'multi-model', 'installed' => 1, 'is_theme' => 0],
            ['id' => 89, 'version' => '1.0', 'slug' => 'all-in-one-package', 'installed' => 0, 'is_theme' => 0],
            ['id' => 90, 'version' => '1.2', 'slug' => 'see-dream-v4', 'installed' => 1, 'is_theme' => 0],
            ['id' => 91, 'version' => '1.3', 'slug' => 'ai-music-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 92, 'version' => '1.1', 'slug' => 'ai-chat-pro-file-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 93, 'version' => '1.2', 'slug' => 'ai-presentation', 'installed' => 1, 'is_theme' => 0],
            ['id' => 94, 'version' => '1.8', 'slug' => 'social-media-agent-dashboard', 'installed' => 1, 'is_theme' => 1],
            ['id' => 95, 'version' => '1.0', 'slug' => 'social-media-agent', 'installed' => 1, 'is_theme' => 0],
            ['id' => 97, 'version' => '1.0', 'slug' => 'ai-chat-pro-memory', 'installed' => 1, 'is_theme' => 1],
        ];

        foreach ($extensions as $extension) {
            DB::table('extensions')->updateOrInsert(
                ['id' => $extension['id']],
                [
                    'version' => $extension['version'],
                    'slug' => $extension['slug'],
                    'installed' => $extension['installed'],
                    'is_theme' => $extension['is_theme'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
