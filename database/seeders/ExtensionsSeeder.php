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
            ['id' => 3, 'version' => '1.0', 'viral-clips', 'installed' => 1, 'is_theme' => 0],
            ['id' => 4, 'version' => '1.0', 'influencer-avatars', 'installed' => 1, 'is_theme' => 0],
            ['id' => 5, 'version' => '2.2', 'marketing-bot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 6, 'version' => '2.0', 'chatbot-voice', 'installed' => 1, 'is_theme' => 0],
            ['id' => 7, 'version' => '1.3', 'live-customizer', 'installed' => 1, 'is_theme' => 0],
            ['id' => 8, 'version' => '5.0', 'chatbot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 9, 'version' => '4.2', 'social-media-extension', 'installed' => 1, 'is_theme' => 0],
            ['id' => 10, 'version' => '2.6', 'ai-chat-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 11, 'version' => '2.3', 'menu', 'installed' => 1, 'is_theme' => 0],
            ['id' => 12, 'version' => '2.2', 'chatbot-agent', 'installed' => 1, 'is_theme' => 0],
            ['id' => 13, 'version' => '1.1', 'mega-menu', 'installed' => 1, 'is_theme' => 0],
            ['id' => 14, 'version' => '2.2', 'advanced-image', 'installed' => 1, 'is_theme' => 0],
            ['id' => 15, 'version' => '1.2', 'whatsapp', 'installed' => 1, 'is_theme' => 0],
            ['id' => 16, 'version' => '1.4', 'onboarding-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 17, 'version' => '1.7', 'openai-realtime-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 18, 'version' => '1.2', 'ai-avatar-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 19, 'version' => '1.0', 'azure-openai', 'installed' => 1, 'is_theme' => 0],
            ['id' => 20, 'version' => '1.3', 'telegram', 'installed' => 1, 'is_theme' => 0],
            ['id' => 21, 'version' => '2.3', 'ai-fall-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 22, 'version' => '1.6', 'ai-realtime-image', 'installed' => 1, 'is_theme' => 0],
            ['id' => 23, 'version' => '2.5', 'chat-share', 'installed' => 1, 'is_theme' => 0],
            ['id' => 24, 'version' => '2.1', 'focus-mode', 'installed' => 1, 'is_theme' => 0],
            ['id' => 25, 'version' => '2.0', 'introductions', 'installed' => 1, 'is_theme' => 0],
            ['id' => 26, 'version' => '2.2', 'flux-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 27, 'version' => '1.0', 'public-announcements', 'installed' => 1, 'is_theme' => 0],
            ['id' => 28, 'version' => '2.1', 'voice-isolator', 'installed' => 1, 'is_theme' => 0],
            ['id' => 29, 'version' => '2.0', 'hubspot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 30, 'version' => '2.0', 'mailchimp-newsletter', 'installed' => 1, 'is_theme' => 0],
            ['id' => 31, 'version' => '2.3', 'ai-product-shot', 'installed' => 1, 'is_theme' => 0],
            ['id' => 32, 'version' => '2.2', 'ai-avatar', 'installed' => 1, 'is_theme' => 0],
            ['id' => 33, 'version' => '2.0', 'maintenance', 'installed' => 1, 'is_theme' => 0],
            ['id' => 34, 'version' => '2.0', 'ai-writer-templates', 'installed' => 1, 'is_theme' => 0],
            ['id' => 35, 'version' => '3.4', 'seo-tool', 'installed' => 1, 'is_theme' => 0],
            ['id' => 36, 'version' => '2.1', 'azure-tts', 'installed' => 1, 'is_theme' => 0],
            ['id' => 37, 'version' => '3.2', 'cryptomus', 'installed' => 1, 'is_theme' => 0],
            ['id' => 38, 'version' => '4.5', 'ai-social-media', 'installed' => 1, 'is_theme' => 0],
            ['id' => 39, 'version' => '3.0', 'wordpress', 'installed' => 1, 'is_theme' => 0],
            ['id' => 40, 'version' => '3.2', 'cloudflare-r2', 'installed' => 1, 'is_theme' => 0],
            ['id' => 41, 'version' => '3.2', 'chat-setting', 'installed' => 1, 'is_theme' => 0],
            ['id' => 42, 'version' => '2.8', 'webchat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 43, 'version' => '2.1', 'plagiarism', 'installed' => 1, 'is_theme' => 0],
            ['id' => 44, 'version' => '2.0', 'newsletter', 'installed' => 1, 'is_theme' => 0],
            ['id' => 45, 'version' => '1.0', 'perplexity', 'installed' => 1, 'is_theme' => 0],
            ['id' => 46, 'version' => '1.4', 'checkout-registration', 'installed' => 1, 'is_theme' => 0],
            ['id' => 47, 'version' => '1.3', 'ai-video-to-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 48, 'version' => '2.3', 'midjourney', 'installed' => 1, 'is_theme' => 0],
            ['id' => 49, 'version' => '1.3', 'ai-music', 'installed' => 1, 'is_theme' => 0],
            ['id' => 50, 'version' => '1.1', 'open-router', 'installed' => 1, 'is_theme' => 0],
            ['id' => 51, 'version' => '1.0', 'only-show-mobile', 'installed' => 1, 'is_theme' => 0],
            ['id' => 52, 'version' => '1.0', 'xero', 'installed' => 1, 'is_theme' => 0],
            ['id' => 53, 'version' => '1.2', 'migration', 'installed' => 1, 'is_theme' => 0],
            ['id' => 54, 'version' => '5.5', 'bolt', 'installed' => 1, 'is_theme' => 1],
            ['id' => 55, 'version' => '4.8', 'modern', 'installed' => 1, 'is_theme' => 1],
            ['id' => 56, 'version' => '1.1', 'social-media-frontend', 'installed' => 1, 'is_theme' => 1],
            ['id' => 57, 'version' => '1.7', 'social-media-dashboard', 'installed' => 1, 'is_theme' => 1],
            ['id' => 58, 'version' => '4.8', 'dark', 'installed' => 1, 'is_theme' => 1],
            ['id' => 59, 'version' => '5.0', 'classic', 'installed' => 1, 'is_theme' => 1],
            ['id' => 60, 'version' => '4.6', 'creative', 'installed' => 1, 'is_theme' => 1],
            ['id' => 61, 'version' => '5.1', 'sleek', 'installed' => 1, 'is_theme' => 1],
            ['id' => 62, 'version' => '1', 'default', 'installed' => 0, 'is_theme' => 1],
            ['id' => 63, 'version' => '1.0', 'mobile', 'installed' => 0, 'is_theme' => 0],
            ['id' => 64, 'version' => '1.0', 'eleven-labs-voice-chat', 'installed' => 0, 'is_theme' => 0],
            ['id' => 65, 'version' => '1.5', 'creative-suite', 'installed' => 1, 'is_theme' => 0],
            ['id' => 66, 'version' => '1.4', 'url-to-video', 'installed' => 1, 'is_theme' => 0],
            ['id' => 67, 'version' => '1.2', 'ai-viral-clips', 'installed' => 1, 'is_theme' => 0],
            ['id' => 68, 'version' => '1.3', 'influencer-avatar', 'installed' => 1, 'is_theme' => 0],
            ['id' => 69, 'version' => '5.1', 'social-media', 'installed' => 1, 'is_theme' => 0],
            ['id' => 70, 'version' => '1.4', 'chatbot-whatsapp', 'installed' => 1, 'is_theme' => 0],
            ['id' => 71, 'version' => '2.2', 'ai-persona', 'installed' => 1, 'is_theme' => 0],
            ['id' => 72, 'version' => '1.2', 'elevenlabs-voice-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 73, 'version' => '1.4', 'chatbot-telegram', 'installed' => 1, 'is_theme' => 0],
            ['id' => 74, 'version' => '2.9', 'ai-video-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 75, 'version' => '1.3', 'announcement', 'installed' => 1, 'is_theme' => 0],
            ['id' => 76, 'version' => '1.7', 'marketing-bot-dashboard', 'installed' => 1, 'is_theme' => 1],
            ['id' => 77, 'version' => '1.2', 'marketing-bot-frontend', 'installed' => 1, 'is_theme' => 1],
            ['id' => 78, 'version' => '1.6', 'canvas', 'installed' => 1, 'is_theme' => 0],
            ['id' => 79, 'version' => '1.1', 'content-manager', 'installed' => 1, 'is_theme' => 0],
            ['id' => 80, 'version' => '1.0', 'ai-replica', 'installed' => 1, 'is_theme' => 0],
            ['id' => 81, 'version' => '1.1', 'chatbot-messenger', 'installed' => 1, 'is_theme' => 0],
            ['id' => 82, 'version' => '1.0', 'speechify-tts', 'installed' => 0, 'is_theme' => 0],
            ['id' => 83, 'version' => '1.3', 'discount-manager', 'installed' => 1, 'is_theme' => 0],
            ['id' => 84, 'version' => '1.0', 'footer-menu', 'installed' => 0, 'is_theme' => 0],
            ['id' => 85, 'version' => '1.2', 'chat-pro-temp-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 86, 'version' => '1.1', 'nano-banana', 'installed' => 1, 'is_theme' => 0],
            ['id' => 87, 'version' => '1.0', '10-must-have-apps-for-ai-video-generation-1756899408', 'installed' => 0, 'is_theme' => 0],
            ['id' => 88, 'version' => '1.2', 'multi-model', 'installed' => 1, 'is_theme' => 0],
            ['id' => 89, 'version' => '1.0', 'all-in-one-package', 'installed' => 0, 'is_theme' => 0],
            ['id' => 90, 'version' => '1.1', 'see-dream-v4', 'installed' => 1, 'is_theme' => 0],
            ['id' => 91, 'version' => '1.2', 'ai-music-pro', 'installed' => 1, 'is_theme' => 0],
            ['id' => 92, 'version' => '1.1', 'ai-chat-pro-file-chat', 'installed' => 1, 'is_theme' => 0],
            ['id' => 93, 'version' => '1.1', 'ai-presentation', 'installed' => 1, 'is_theme' => 0],
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
