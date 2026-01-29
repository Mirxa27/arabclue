<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Property;
use App\Models\AIContentQueue;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\GenerateAIContentRequest;
use App\Http\Requests\BulkGenerateAIContentRequest;
use App\Http\Requests\ApproveAIContentRequest;
use App\Http\Requests\RegenerateAIContentRequest;

/**
 * Admin AI Content Controller - Intelligent Content Generation System
 * 
 * Provides AI-powered content generation capabilities for various
 * platform content types including property descriptions, marketing
 * copy, blog posts, and dynamic page content
 * 
 * @package App\Http\Controllers\Admin
 * @version 1.0.0
 */
class AIContentController extends Controller
{
    /**
     * AI Service instance
     */
    protected AIService $aiService;
    
    /**
     * Controller constructor
     */
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware(['auth', 'admin', 'content.generator']);
    }
    
    /**
     * Display AI content generation dashboard
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $contentStats = [
            'total_generated' => ContentBlock::where('ai_generated', true)->count(),
            'pending_queue' => AIContentQueue::where('status', 'pending')->count(),
            'tokens_used_today' => Cache::get('ai_token_usage_' . date('Y-m-d'), 0),
            'recent_generations' => ContentBlock::where('ai_generated', true)
                ->latest()
                ->limit(10)
                ->get()
        ];
        
        $contentTypes = $this->getAvailableContentTypes();
        
        return view('admin.ai-content.index', compact('contentStats', 'contentTypes'));
    }
    
    /**
     * Show content generation form
     * 
     * @param string $type Content type
     * @return \Illuminate\View\View
     */
    public function create(string $type)
    {
        $contentType = $this->getContentTypeConfig($type);
        
        if (!$contentType) {
            abort(404, 'Invalid content type');
        }
        
        $data = ['contentType' => $contentType];
        
        // Load additional data based on content type
        switch ($type) {
            case 'property_description':
                $data['properties'] = Property::where('ai_generated_description', null)
                    ->orWhere('description', '')
                    ->limit(100)
                    ->get(['id', 'title', 'property_type', 'city']);
                break;
                
            case 'marketing_copy':
                $data['campaigns'] = $this->getMarketingCampaigns();
                break;
                
            case 'blog_post':
                $data['categories'] = $this->getBlogCategories();
                break;
        }
        
        return view('admin.ai-content.create', $data);
    }
    
    /**
     * Generate content using AI
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(GenerateAIContentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $results = [];
            $variationCount = $validated['generate_variations'] 
                ? ($validated['variation_count'] ?? 3) 
                : 1;
            
            for ($i = 0; $i < $variationCount; $i++) {
                $response = $this->aiService->generateContent(
                    $validated['type'],
                    array_merge($validated['parameters'], [
                        'variation_index' => $i,
                        'total_variations' => $variationCount
                    ]),
                    $validated['context'] ?? []
                );
                
                $results[] = [
                    'content' => $response['content'],
                    'metadata' => $response,
                    'variation' => $i + 1
                ];
            }
            
            // Store generated content
            $contentBlock = $this->storeGeneratedContent(
                $validated['type'],
                $results,
                $validated['parameters']
            );
            
            $this->logActivity('ai_content_generated', [
                'type' => $validated['type'],
                'variations' => $variationCount,
                'content_block_id' => $contentBlock->id
            ]);
            
            return $this->successResponse([
                'results' => $results,
                'content_block_id' => $contentBlock->id,
                'token_usage' => $this->aiService->getTokenUsage()
            ], 'Content generated successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to generate content: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Bulk generate content for multiple items
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkGenerate(BulkGenerateAIContentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            $queueItems = [];
            
            foreach ($validated['items'] as $item) {
                $queueItem = AIContentQueue::create([
                    'content_type' => $validated['type'],
                    'parameters' => $item['parameters'],
                    'target_identifier' => $item['id'],
                    'requested_by' => auth()->id(),
                    'status' => $request->input('queue_processing') ? 'pending' : 'processing'
                ]);
                
                $queueItems[] = $queueItem;
            }
            
            DB::commit();
            
            if (!$request->input('queue_processing')) {
                // Process immediately
                $results = [];
                foreach ($queueItems as $queueItem) {
                    try {
                        $response = $this->aiService->generateContent(
                            $queueItem->content_type,
                            $queueItem->parameters
                        );
                        
                        $queueItem->update([
                            'status' => 'completed',
                            'result' => $response,
                            'processed_at' => now()
                        ]);
                        
                        $results[] = [
                            'id' => $queueItem->target_identifier,
                            'success' => true,
                            'content' => $response['content']
                        ];
                        
                    } catch (\Exception $e) {
                        $queueItem->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage()
                        ]);
                        
                        $results[] = [
                            'id' => $queueItem->target_identifier,
                            'success' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }
                
                return $this->successResponse([
                    'results' => $results,
                    'processed' => count(array_filter($results, fn($r) => $r['success'])),
                    'failed' => count(array_filter($results, fn($r) => !$r['success']))
                ], 'Bulk generation completed');
            }
            
            // Queue for background processing
            foreach ($queueItems as $item) {
                dispatch(new \App\Jobs\ProcessAIContentQueue($item));
            }
            
            return $this->successResponse([
                'queued_items' => count($queueItems),
                'message' => 'Items queued for processing'
            ], 'Bulk generation queued successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to queue bulk generation: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Review and approve AI-generated content
     * 
     * @param ContentBlock $contentBlock
     * @return JsonResponse
     */
    public function review(ContentBlock $contentBlock): JsonResponse
    {
        if (!$contentBlock->ai_generated) {
            return $this->errorResponse('This content was not AI-generated', 400);
        }
        
        $contentBlock->load('reviewedBy');
        
        return $this->successResponse([
            'content' => $contentBlock,
            'variations' => json_decode($contentBlock->content['variations'] ?? '[]', true)
        ]);
    }
    
    /**
     * Approve AI-generated content
     * 
     * @param Request $request
     * @param ContentBlock $contentBlock
     * @return JsonResponse
     */
    public function approve(ApproveAIContentRequest $request, ContentBlock $contentBlock): JsonResponse
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            // Update content block
            $contentBlock->update([
                'human_reviewed' => true,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()
            ]);
            
            if (isset($validated['edited_content'])) {
                $contentBlock->update([
                    'content' => array_merge((array) ($contentBlock->content ?? []), [ // Ensure first arg is array
                        'final' => $validated['edited_content']
                    ])
                ]);
            }
            
            // Apply to target if requested
            if ($request->input('apply_to_target')) {
                $this->applyContentToTarget($contentBlock);
            }
            
            DB::commit();
            
            $this->logActivity('ai_content_approved', [
                'content_block_id' => $contentBlock->id
            ]);
            
            return $this->successResponse(null, 'Content approved successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to approve content: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Regenerate content with new parameters
     * 
     * @param Request $request
     * @param ContentBlock $contentBlock
     * @return JsonResponse
     */
    public function regenerate(RegenerateAIContentRequest $request, ContentBlock $contentBlock): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            // Merge with original parameters
            $parameters = array_merge(
                $contentBlock->ai_metadata['parameters'] ?? [],
                $validated['parameters'] ?? []
            );
            
            if ($validated['feedback']) {
                $parameters['user_feedback'] = $validated['feedback'];
            }
            
            $response = $this->aiService->generateContent(
                $contentBlock->block_type,
                $parameters,
                ['previous_content' => $contentBlock->content]
            );
            
            // Create new version
            $newBlock = $contentBlock->replicate();
            $newBlock->content = array_merge((array) ($contentBlock->content ?? []), [ // Ensure first arg is array
                'regenerated' => $response['content']
            ]);
            $newBlock->ai_metadata = array_merge((array) ($contentBlock->ai_metadata ?? []), [ // Ensure first arg is array
                'regeneration_feedback' => $validated['feedback'],
                'regenerated_at' => now()
            ]);
            $newBlock->human_reviewed = false;
            $newBlock->save();
            
            return $this->successResponse([
                'new_content' => $response['content'],
                'content_block_id' => $newBlock->id
            ], 'Content regenerated successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to regenerate content: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Get AI content generation queue status
     * 
     * @return JsonResponse
     */
    public function queueStatus(): JsonResponse
    {
        $stats = [
            'pending' => AIContentQueue::where('status', 'pending')->count(),
            'processing' => AIContentQueue::where('status', 'processing')->count(),
            'completed_today' => AIContentQueue::where('status', 'completed')
                ->whereDate('processed_at', today())
                ->count(),
            'failed_today' => AIContentQueue::where('status', 'failed')
                ->whereDate('updated_at', today())
                ->count(),
            'average_processing_time' => AIContentQueue::where('status', 'completed')
                ->whereNotNull('processed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as avg_time')
                ->value('avg_time')
        ];
        
        $recentItems = AIContentQueue::with('requestedBy')
            ->latest()
            ->limit(20)
            ->get();
        
        return $this->successResponse([
            'stats' => $stats,
            'recent_items' => $recentItems
        ]);
    }
    
    /**
     * Process content queue manually
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function processQueue(Request $request): JsonResponse
    {
        $batchSize = $request->input('batch_size', 10);
        
        try {
            $results = $this->aiService->processContentQueue($batchSize);
            
            return $this->successResponse([
                'results' => $results,
                'message' => "Processed {$results['processed']} items"
            ]);
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to process queue: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Get AI usage analytics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->input('period', 'week');
        
        $analytics = [
            'token_usage' => $this->getTokenUsageAnalytics($period),
            'content_generation' => $this->getContentGenerationAnalytics($period),
            'cost_analysis' => $this->getCostAnalytics($period),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
        
        return $this->successResponse($analytics);
    }
    
    /**
     * Store generated content
     * 
     * @param string $type Content type
     * @param array $results Generation results
     * @param array $parameters Original parameters
     * @return ContentBlock
     */
    protected function storeGeneratedContent(string $type, array $results, array $parameters): ContentBlock
    {
        $identifier = $this->generateContentIdentifier($type, $parameters);
        
        return ContentBlock::updateOrCreate(
            ['identifier' => $identifier],
            [
                'block_type' => $type,
                'content' => [
                    'variations' => $results,
                    'selected' => 0,
                    'parameters' => $parameters
                ],
                'ai_metadata' => [
                    'model' => $results[0]['metadata']['model'] ?? 'gpt-4',
                    'tokens_used' => array_sum(array_column(array_column($results, 'metadata'), 'tokens_used')),
                    'confidence_scores' => array_column(array_column($results, 'metadata'), 'confidence_score'),
                    'generated_at' => now(),
                    'parameters' => $parameters
                ],
                'ai_model_used' => $results[0]['metadata']['model'] ?? 'gpt-4',
                'ai_confidence_score' => array_sum(array_column(array_column($results, 'metadata'), 'confidence_score')) / count($results),
                'ai_generated' => true,
                'human_reviewed' => false
            ]
        );
    }
    
    /**
     * Apply content to target entity
     * 
     * @param ContentBlock $contentBlock
     */
    protected function applyContentToTarget(ContentBlock $contentBlock): void
    {
        $content = $contentBlock->content['final'] ?? 
                  $contentBlock->content['variations'][0]['content'] ?? 
                  null;
        
        if (!$content) {
            return;
        }
        
        switch ($contentBlock->block_type) {
            case 'property_description':
                if (isset($contentBlock->ai_metadata['parameters']['property_id'])) {
                    Property::find($contentBlock->ai_metadata['parameters']['property_id'])
                        ->update([
                            'description' => $content['description'] ?? '',
                            'ai_generated_description' => $content
                        ]);
                }
                break;
                
            // Add more cases for different content types
        }
    }
    
    /**
     * Generate unique content identifier
     * 
     * @param string $type
     * @param array $parameters
     * @return string
     */
    protected function generateContentIdentifier(string $type, array $parameters): string
    {
        return $type . '_' . md5(json_encode($parameters)) . '_' . time();
    }
    
    /**
     * Get available content types
     * 
     * @return array
     */
    protected function getAvailableContentTypes(): array
    {
        return [
            'property_description' => [
                'name' => 'Property Description',
                'icon' => 'fas fa-home',
                'description' => 'Generate engaging property descriptions'
            ],
            'property_title' => [
                'name' => 'Property Title',
                'icon' => 'fas fa-heading',
                'description' => 'Create catchy property titles'
            ],
            'marketing_copy' => [
                'name' => 'Marketing Copy',
                'icon' => 'fas fa-bullhorn',
                'description' => 'Generate marketing content and campaigns'
            ],
            'email_template' => [
                'name' => 'Email Template',
                'icon' => 'fas fa-envelope',
                'description' => 'Create professional email templates'
            ],
            'blog_post' => [
                'name' => 'Blog Post',
                'icon' => 'fas fa-blog',
                'description' => 'Generate blog posts and articles'
            ],
            'faq_answer' => [
                'name' => 'FAQ Answer',
                'icon' => 'fas fa-question-circle',
                'description' => 'Create comprehensive FAQ responses'
            ],
            'review_response' => [
                'name' => 'Review Response',
                'icon' => 'fas fa-comment-dots',
                'description' => 'Generate professional review responses'
            ],
            'social_media' => [
                'name' => 'Social Media',
                'icon' => 'fas fa-share-alt',
                'description' => 'Create social media content'
            ],
            'meta_description' => [
                'name' => 'Meta Description',
                'icon' => 'fas fa-search',
                'description' => 'Generate SEO meta descriptions'
            ]
        ];
    }
    
    /**
     * Get content type configuration
     * 
     * @param string $type
     * @return array|null
     */
    protected function getContentTypeConfig(string $type): ?array
    {
        $types = $this->getAvailableContentTypes();
        
        if (!isset($types[$type])) {
            return null;
        }
        
        $config = $types[$type];
        $config['key'] = $type;
        
        // Add field configurations
        $config['fields'] = $this->getContentTypeFields($type);
        
        return $config;
    }
    
    /**
     * Get content type fields
     * 
     * @param string $type
     * @return array
     */
    protected function getContentTypeFields(string $type): array
    {
        $fields = [
            'property_description' => [
                ['name' => 'property_id', 'type' => 'select', 'label' => 'Property', 'required' => true],
                ['name' => 'target_audience', 'type' => 'select', 'label' => 'Target Audience', 'options' => ['families', 'business', 'couples', 'groups']],
                ['name' => 'tone', 'type' => 'select', 'label' => 'Tone', 'options' => ['professional', 'friendly', 'luxury', 'casual']],
                ['name' => 'highlight_features', 'type' => 'tags', 'label' => 'Features to Highlight']
            ],
            'marketing_copy' => [
                ['name' => 'campaign_type', 'type' => 'select', 'label' => 'Campaign Type', 'required' => true],
                ['name' => 'target_audience', 'type' => 'text', 'label' => 'Target Audience'],
                ['name' => 'key_message', 'type' => 'textarea', 'label' => 'Key Message'],
                ['name' => 'call_to_action', 'type' => 'text', 'label' => 'Call to Action']
            ],
            'blog_post' => [
                ['name' => 'topic', 'type' => 'text', 'label' => 'Topic', 'required' => true],
                ['name' => 'category', 'type' => 'select', 'label' => 'Category'],
                ['name' => 'keywords', 'type' => 'tags', 'label' => 'SEO Keywords'],
                ['name' => 'word_count', 'type' => 'number', 'label' => 'Target Word Count', 'default' => 1000]
            ]
        ];
        
        return $fields[$type] ?? [];
    }
    
    /**
     * Get marketing campaigns
     * 
     * @return array
     */
    protected function getMarketingCampaigns(): array
    {
        return [
            'seasonal' => 'Seasonal Promotion',
            'new_launch' => 'New Property Launch',
            'referral' => 'Referral Program',
            'retention' => 'Customer Retention',
            'acquisition' => 'New Customer Acquisition'
        ];
    }
    
    /**
     * Get blog categories
     * 
     * @return array
     */
    protected function getBlogCategories(): array
    {
        return [
            'travel_guides' => 'Travel Guides',
            'local_insights' => 'Local Insights',
            'property_tips' => 'Property Tips',
            'investment' => 'Investment Advice',
            'saudi_culture' => 'Saudi Culture',
            'news' => 'News & Updates'
        ];
    }
    
    /**
     * Get token usage analytics
     * 
     * @param string $period
     * @return array
     */
    protected function getTokenUsageAnalytics(string $period): array
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 1);
        $usage = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $usage[$date] = Cache::get('ai_token_usage_' . $date, 0);
        }
        
        return [
            'daily_usage' => array_reverse($usage),
            'total' => array_sum($usage),
            'average' => $days > 0 ? array_sum($usage) / $days : 0
        ];
    }
    
    /**
     * Get content generation analytics
     * 
     * @param string $period
     * @return array
     */
    protected function getContentGenerationAnalytics(string $period): array
    {
        $startDate = $period === 'week' ? now()->subWeek() : now()->subMonth();
        
        return ContentBlock::where('ai_generated', true)
            ->where('created_at', '>=', $startDate)
            ->groupBy('block_type')
            ->selectRaw('block_type, COUNT(*) as count, AVG(ai_confidence_score) as avg_confidence')
            ->get()
            ->keyBy('block_type')
            ->toArray();
    }
    
    /**
     * Get cost analytics
     * 
     * @param string $period
     * @return array
     */
    protected function getCostAnalytics(string $period): array
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 1);
        $totalCost = 0;
        
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $tokens = Cache::get('ai_token_usage_' . $date, 0);
            $totalCost += ($tokens * 0.03) / 1000; // Rough estimate
        }
        
        return [
            'estimated_cost' => round($totalCost, 2),
            'cost_per_generation' => ContentBlock::where('ai_generated', true)
                ->where('created_at', '>=', now()->subDays($days))
                ->count() > 0 
                ? round($totalCost / ContentBlock::where('ai_generated', true)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->count(), 4)
                : 0
        ];
    }
    
    /**
     * Get performance metrics
     * 
     * @return array
     */
    protected function getPerformanceMetrics(): array
    {
        return [
            'approval_rate' => ContentBlock::where('ai_generated', true)
                ->where('human_reviewed', true)
                ->count() / max(ContentBlock::where('ai_generated', true)->count(), 1) * 100,
            'average_confidence' => ContentBlock::where('ai_generated', true)
                ->avg('ai_confidence_score') ?? 0,
            'regeneration_rate' => AIContentQueue::where('retry_count', '>', 0)
                ->count() / max(AIContentQueue::count(), 1) * 100
        ];
    }
}
