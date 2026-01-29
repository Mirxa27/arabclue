<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\PageLayout;
use App\Models\UiComponent;
use App\Models\ContentBlock;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Admin Theme Controller - Dynamic UI Customization Engine
 * 
 * Provides comprehensive theme management capabilities allowing
 * administrators to customize every aspect of the frontend
 * without code deployment
 * 
 * @package App\Http\Controllers\Admin
 * @version 1.0.0
 */
class ThemeController extends Controller
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
        $this->middleware(['auth', 'admin', 'theme.editor']);
    }
    
    /**
     * Display theme management dashboard
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $themes = Theme::with('creator')
            ->withCount(['pageLayouts', 'uiComponents'])
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        $activeTheme = Theme::active();
        
        return view('admin.themes.index', compact('themes', 'activeTheme'));
    }
    
    /**
     * Show theme editor interface
     * 
     * @param Theme $theme
     * @return \Illuminate\View\View
     */
    public function edit(Theme $theme)
    {
        $theme->load(['pageLayouts', 'uiComponents', 'revisions']);
        
        // Get component templates
        $componentTemplates = $this->getComponentTemplates();
        
        // Get available fonts
        $availableFonts = $this->getAvailableFonts();
        
        // Get animation presets
        $animationPresets = $this->getAnimationPresets();
        
        return view('admin.themes.editor', compact(
            'theme',
            'componentTemplates',
            'availableFonts',
            'animationPresets'
        ));
    }
    
    /**
     * Update theme configuration via AJAX
     * 
     * @param Request $request
     * @param Theme $theme
     * @return JsonResponse
     */
    public function update(Request $request, Theme $theme): JsonResponse
    {
        $validated = $request->validate([
            'section' => 'required|in:colors,typography,spacing,components,animations,breakpoints',
            'data' => 'required|array',
            'description' => 'nullable|string|max:255'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Store current state for revision
            $previousState = $theme->{$validated['section']};
            
            // Update theme section
            $theme->update([
                $validated['section'] => array_merge(
                    $theme->{$validated['section']} ?? [],
                    $validated['data']
                )
            ]);
            
            // Create revision record
            $theme->revisions()->create([
                'changes' => [
                    'previous' => $previousState,
                    'new' => $theme->{$validated['section']}
                ],
                'change_type' => $validated['section'],
                'description' => $validated['description'],
                'user_id' => auth()->id()
            ]);
            
            // Clear theme cache
            cache()->forget('active_theme');
            cache()->forget("theme_{$theme->id}");
            
            DB::commit();
            
            // Generate preview CSS
            $previewCss = $theme->compileCss();
            
            return $this->successResponse([
                'theme' => $theme->fresh(),
                'preview_css' => $previewCss,
                'message' => 'Theme updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to update theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Live preview endpoint for theme changes
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'changes' => 'required|array'
        ]);
        
        $theme = Theme::findOrFail($validated['theme_id']);
        
        // Create temporary theme instance with changes
        $tempTheme = $theme->replicate();
        
        foreach ($validated['changes'] as $section => $data) {
            if (in_array($section, ['color_scheme', 'typography', 'spacing', 'components', 'animations', 'breakpoints'])) {
                $tempTheme->$section = array_merge($theme->$section ?? [], $data);
            }
        }
        
        // Generate preview CSS
        $previewCss = $tempTheme->compileCss();
        
        return $this->successResponse([
            'preview_css' => $previewCss,
            'preview_data' => $tempTheme->only(['color_scheme', 'typography', 'spacing'])
        ]);
    }
    
    /**
     * Create new theme
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
            'description' => 'nullable|string',
            'base_theme_id' => 'nullable|exists:themes,id',
            'use_ai_generation' => 'boolean'
        ]);
        
        DB::beginTransaction();
        
        try {
            if ($request->input('use_ai_generation')) {
                // Generate theme using AI
                $themeConfig = $this->generateThemeWithAI($validated['name'], $request->all());
                $validated = array_merge($validated, $themeConfig);
            } elseif ($validated['base_theme_id']) {
                // Clone existing theme
                $baseTheme = Theme::findOrFail($validated['base_theme_id']);
                $theme = $baseTheme->duplicate($validated['name']);
            } else {
                // Create blank theme with defaults
                $validated['color_scheme'] = $this->getDefaultColorScheme();
                $validated['typography'] = $this->getDefaultTypography();
                $validated['spacing'] = $this->getDefaultSpacing();
                $validated['components'] = [];
                $validated['animations'] = [];
                $validated['breakpoints'] = $this->getDefaultBreakpoints();
            }
            
            if (!isset($theme)) {
                $validated['created_by'] = auth()->id();
                $validated['slug'] = Str::slug($validated['name']);
                
                $theme = Theme::create($validated);
            }
            
            // Create default page layouts
            $this->createDefaultPageLayouts($theme);
            
            DB::commit();
            
            $this->logActivity('theme_created', ['theme_id' => $theme->id]);
            
            return $this->successResponse([
                'theme' => $theme->load('pageLayouts'),
                'redirect' => route('admin.themes.edit', $theme)
            ], 'Theme created successfully', 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to create theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Activate theme
     * 
     * @param Theme $theme
     * @return JsonResponse
     */
    public function activate(Theme $theme): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Deactivate current active theme
            Theme::where('is_active', true)->update(['is_active' => false]);
            
            // Activate selected theme
            $theme->update(['is_active' => true]);
            
            // Clear all theme-related caches
            cache()->tags(['themes', 'pages', 'components'])->flush();
            
            DB::commit();
            
            $this->logActivity('theme_activated', ['theme_id' => $theme->id]);
            
            return $this->successResponse(null, 'Theme activated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to activate theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Duplicate theme
     * 
     * @param Theme $theme
     * @return JsonResponse
     */
    public function duplicate(Theme $theme): JsonResponse
    {
        try {
            $newName = $theme->name . ' (Copy)';
            $duplicatedTheme = $theme->duplicate($newName);
            
            // Duplicate page layouts
            foreach ($theme->pageLayouts as $layout) {
                $newLayout = $layout->replicate();
                $newLayout->theme_id = $duplicatedTheme->id;
                $newLayout->save();
            }
            
            // Duplicate UI components
            foreach ($theme->uiComponents as $component) {
                $newComponent = $component->replicate();
                $newComponent->theme_id = $duplicatedTheme->id;
                $newComponent->save();
            }
            
            $this->logActivity('theme_duplicated', [
                'original_theme_id' => $theme->id,
                'new_theme_id' => $duplicatedTheme->id
            ]);
            
            return $this->successResponse([
                'theme' => $duplicatedTheme,
                'redirect' => route('admin.themes.edit', $duplicatedTheme)
            ], 'Theme duplicated successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to duplicate theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Export theme configuration
     * 
     * @param Theme $theme
     * @return \Illuminate\Http\Response
     */
    public function export(Theme $theme)
    {
        $exportData = [
            'theme' => $theme->export(),
            'page_layouts' => $theme->pageLayouts->map->export(),
            'ui_components' => $theme->uiComponents->map->export(),
            'version' => '1.0.0',
            'exported_at' => now()->toIso8601String()
        ];
        
        $json = json_encode($exportData, JSON_PRETTY_PRINT);
        $filename = Str::slug($theme->name) . '-theme-export.json';
        
        return response($json, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Import theme configuration
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'theme_file' => 'required|file|mimes:json|max:5120'
        ]);
        
        try {
            $content = file_get_contents($request->file('theme_file')->getRealPath());
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON file');
            }
            
            DB::beginTransaction();
            
            // Import theme
            $theme = Theme::import($data['theme'], auth()->id());
            
            // Import page layouts
            if (isset($data['page_layouts'])) {
                foreach ($data['page_layouts'] as $layoutData) {
                    PageLayout::import($layoutData, $theme->id);
                }
            }
            
            // Import UI components
            if (isset($data['ui_components'])) {
                foreach ($data['ui_components'] as $componentData) {
                    UiComponent::import($componentData, $theme->id);
                }
            }
            
            DB::commit();
            
            $this->logActivity('theme_imported', ['theme_id' => $theme->id]);
            
            return $this->successResponse([
                'theme' => $theme,
                'redirect' => route('admin.themes.edit', $theme)
            ], 'Theme imported successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to import theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Delete theme
     * 
     * @param Theme $theme
     * @return JsonResponse
     */
    public function destroy(Theme $theme): JsonResponse
    {
        if ($theme->is_active) {
            return $this->errorResponse('Cannot delete active theme', 400);
        }
        
        try {
            $theme->delete();
            
            $this->logActivity('theme_deleted', ['theme_id' => $theme->id]);
            
            return $this->successResponse(null, 'Theme deleted successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete theme: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Generate theme using AI
     * 
     * @param string $name Theme name
     * @param array $parameters Generation parameters
     * @return array Theme configuration
     */
    protected function generateThemeWithAI(string $name, array $parameters): array
    {
        $response = $this->aiService->generateContent('theme_generation', [
            'name' => $name,
            'style' => $parameters['style'] ?? 'modern',
            'primary_color' => $parameters['primary_color'] ?? '#667eea',
            'mood' => $parameters['mood'] ?? 'professional',
            'target_audience' => $parameters['target_audience'] ?? 'general'
        ]);
        
        $generatedTheme = $response['content'];
        
        return [
            'color_scheme' => $generatedTheme['colors'] ?? $this->getDefaultColorScheme(),
            'typography' => $generatedTheme['typography'] ?? $this->getDefaultTypography(),
            'spacing' => $generatedTheme['spacing'] ?? $this->getDefaultSpacing(),
            'components' => $generatedTheme['components'] ?? [],
            'animations' => $generatedTheme['animations'] ?? [],
            'breakpoints' => $generatedTheme['breakpoints'] ?? $this->getDefaultBreakpoints()
        ];
    }
    
    /**
     * Create default page layouts for new theme
     * 
     * @param Theme $theme
     */
    protected function createDefaultPageLayouts(Theme $theme): void
    {
        $defaultLayouts = [
            'home' => [
                'title' => 'Homepage',
                'sections' => [
                    ['type' => 'hero', 'props' => []],
                    ['type' => 'features', 'props' => []],
                    ['type' => 'properties', 'props' => ['featured' => true]],
                    ['type' => 'testimonials', 'props' => []],
                    ['type' => 'cta', 'props' => []]
                ]
            ],
            'property_listing' => [
                'title' => 'Property Listing',
                'sections' => [
                    ['type' => 'search_hero', 'props' => []],
                    ['type' => 'filters', 'props' => []],
                    ['type' => 'property_grid', 'props' => []],
                    ['type' => 'map', 'props' => []]
                ]
            ],
            'property_detail' => [
                'title' => 'Property Detail',
                'sections' => [
                    ['type' => 'gallery', 'props' => []],
                    ['type' => 'property_info', 'props' => []],
                    ['type' => 'amenities', 'props' => []],
                    ['type' => 'location', 'props' => []],
                    ['type' => 'reviews', 'props' => []],
                    ['type' => 'similar_properties', 'props' => []]
                ]
            ]
        ];
        
        foreach ($defaultLayouts as $identifier => $config) {
            $theme->pageLayouts()->create([
                'page_identifier' => $identifier,
                'title' => $config['title'],
                'sections' => $config['sections'],
                'is_published' => true
            ]);
        }
    }
    
    /**
     * Get component templates
     * 
     * @return array
     */
    protected function getComponentTemplates(): array
    {
        return [
            'hero' => [
                'name' => 'Hero Section',
                'props' => ['title', 'subtitle', 'cta_text', 'background_image'],
                'variants' => ['centered', 'left-aligned', 'with-search', 'video-background']
            ],
            'features' => [
                'name' => 'Features Grid',
                'props' => ['features', 'columns', 'icon_style'],
                'variants' => ['2-column', '3-column', '4-column', 'with-icons']
            ],
            'property_card' => [
                'name' => 'Property Card',
                'props' => ['show_price', 'show_rating', 'image_aspect_ratio'],
                'variants' => ['standard', 'compact', 'detailed', 'horizontal']
            ],
            'testimonial' => [
                'name' => 'Testimonial',
                'props' => ['layout', 'show_rating', 'show_image'],
                'variants' => ['card', 'quote', 'minimal', 'with-image']
            ],
            'cta' => [
                'name' => 'Call to Action',
                'props' => ['title', 'description', 'button_text', 'style'],
                'variants' => ['centered', 'split', 'minimal', 'with-image']
            ]
        ];
    }
    
    /**
     * Get available fonts
     * 
     * @return array
     */
    protected function getAvailableFonts(): array
    {
        return [
            'system' => [
                'name' => 'System Font Stack',
                'value' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
            ],
            'inter' => [
                'name' => 'Inter',
                'value' => '"Inter", sans-serif',
                'import' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
            ],
            'poppins' => [
                'name' => 'Poppins',
                'value' => '"Poppins", sans-serif',
                'import' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap'
            ],
            'playfair' => [
                'name' => 'Playfair Display',
                'value' => '"Playfair Display", serif',
                'import' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap'
            ],
            'montserrat' => [
                'name' => 'Montserrat',
                'value' => '"Montserrat", sans-serif',
                'import' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap'
            ]
        ];
    }
    
    /**
     * Get animation presets
     * 
     * @return array
     */
    protected function getAnimationPresets(): array
    {
        return [
            'fade_in' => [
                'name' => 'Fade In',
                'keyframes' => [
                    '0%' => ['opacity' => '0'],
                    '100%' => ['opacity' => '1']
                ]
            ],
            'slide_up' => [
                'name' => 'Slide Up',
                'keyframes' => [
                    '0%' => ['opacity' => '0', 'transform' => 'translateY(20px)'],
                    '100%' => ['opacity' => '1', 'transform' => 'translateY(0)']
                ]
            ],
            'scale_in' => [
                'name' => 'Scale In',
                'keyframes' => [
                    '0%' => ['opacity' => '0', 'transform' => 'scale(0.9)'],
                    '100%' => ['opacity' => '1', 'transform' => 'scale(1)']
                ]
            ],
            'rotate_in' => [
                'name' => 'Rotate In',
                'keyframes' => [
                    '0%' => ['opacity' => '0', 'transform' => 'rotate(-10deg)'],
                    '100%' => ['opacity' => '1', 'transform' => 'rotate(0)']
                ]
            ]
        ];
    }
    
    /**
     * Get default color scheme
     * 
     * @return array
     */
    protected function getDefaultColorScheme(): array
    {
        return [
            'primary' => '#667eea',
            'primary-dark' => '#5a67d8',
            'primary-light' => '#7f9cf5',
            'secondary' => '#764ba2',
            'secondary-dark' => '#6b4691',
            'secondary-light' => '#8b5bb3',
            'accent' => '#f6ad55',
            'success' => '#48bb78',
            'warning' => '#ed8936',
            'error' => '#f56565',
            'info' => '#4299e1',
            'background' => '#ffffff',
            'surface' => '#f7fafc',
            'text-primary' => '#1a202c',
            'text-secondary' => '#718096',
            'border' => '#e2e8f0'
        ];
    }
    
    /**
     * Get default typography
     * 
     * @return array
     */
    protected function getDefaultTypography(): array
    {
        return [
            'font-family-base' => '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
            'font-family-heading' => '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
            'font-size-base' => '16px',
            'line-height-base' => '1.5',
            'h1' => [
                'font-size' => '2.5rem',
                'font-weight' => '700',
                'line-height' => '1.2',
                'margin-bottom' => '1rem'
            ],
            'h2' => [
                'font-size' => '2rem',
                'font-weight' => '600',
                'line-height' => '1.3',
                'margin-bottom' => '0.875rem'
            ],
            'h3' => [
                'font-size' => '1.75rem',
                'font-weight' => '600',
                'line-height' => '1.4',
                'margin-bottom' => '0.75rem'
            ],
            'h4' => [
                'font-size' => '1.5rem',
                'font-weight' => '500',
                'line-height' => '1.4',
                'margin-bottom' => '0.625rem'
            ],
            'h5' => [
                'font-size' => '1.25rem',
                'font-weight' => '500',
                'line-height' => '1.5',
                'margin-bottom' => '0.5rem'
            ],
            'h6' => [
                'font-size' => '1.125rem',
                'font-weight' => '500',
                'line-height' => '1.5',
                'margin-bottom' => '0.5rem'
            ]
        ];
    }
    
    /**
     * Get default spacing scale
     * 
     * @return array
     */
    protected function getDefaultSpacing(): array
    {
        return [
            'padding' => [
                '0' => '0',
                '1' => '0.25rem',
                '2' => '0.5rem',
                '3' => '0.75rem',
                '4' => '1rem',
                '5' => '1.25rem',
                '6' => '1.5rem',
                '8' => '2rem',
                '10' => '2.5rem',
                '12' => '3rem',
                '16' => '4rem',
                '20' => '5rem',
                '24' => '6rem'
            ],
            'margin' => [
                '0' => '0',
                '1' => '0.25rem',
                '2' => '0.5rem',
                '3' => '0.75rem',
                '4' => '1rem',
                '5' => '1.25rem',
                '6' => '1.5rem',
                '8' => '2rem',
                '10' => '2.5rem',
                '12' => '3rem',
                '16' => '4rem',
                '20' => '5rem',
                '24' => '6rem',
                'auto' => 'auto'
            ],
            'gap' => [
                '0' => '0',
                '1' => '0.25rem',
                '2' => '0.5rem',
                '3' => '0.75rem',
                '4' => '1rem',
                '5' => '1.25rem',
                '6' => '1.5rem',
                '8' => '2rem',
                '10' => '2.5rem',
                '12' => '3rem'
            ]
        ];
    }
    
    /**
     * Get default breakpoints
     * 
     * @return array
     */
    protected function getDefaultBreakpoints(): array
    {
        return [
            'sm' => '640px',
            'md' => '768px', 
            'lg' => '1024px',
            'xl' => '1280px',
            '2xl' => '1536px'
        ];
    }
}
