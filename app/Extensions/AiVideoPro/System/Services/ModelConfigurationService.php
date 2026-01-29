<?php

namespace App\Extensions\AiVideoPro\System\Services;

use App\Domains\Entity\Enums\EntityEnum;
use App\Packages\FalAI\Enums\AspectRatioEnum;

class ModelConfigurationService
{
    public static function getConfig(): array
    {
        return [
            'sora' => [
                'label'     => __('Generate video with Sora'),
                'isActive'  => setting('sora_active', 1) == 1,
                'subModels' => [
                    'sora2' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::SORA_2->value => [
                                'label'  => EntityEnum::SORA_2->label(),
                                'inputs' => [
                                    self::promptInput(),
                                    self::imageUpload(null, 'image_url', __('Reference Image (optional) — must match the requested width and height'), false),
                                    self::selectInput('sora_seconds', __('Duration'), [
                                        ['value' => '4', 'label' => '4 seconds'],
                                        ['value' => '8', 'label' => '8 seconds'],
                                        ['value' => '12', 'label' => '12 seconds'],
                                    ], '4', __('Select video duration (4, 8, or 12 seconds).')),
                                    self::selectInput('sora_size', __('Size'), [
                                        ['value' => '720x1280', 'label' => '720x1280'],
                                        ['value' => '1280x720', 'label' => '1280x720'],
                                        ['value' => '1024×1792', 'label' => '1024×1792'],
                                        ['value' => '1792×1024', 'label' => '1792×1024'],
                                    ], '720x1280'),
                                ],
                            ],
                            EntityEnum::SORA_2_PRO->value => [
                                'label'  => EntityEnum::SORA_2_PRO->label(),
                                'inputs' => [
                                    self::promptInput(),
                                    self::imageUpload(null, 'image_url', __('Reference Image (optional) — must match the requested width and height'), false),
                                    self::selectInput('sora_seconds', __('Duration'), [
                                        ['value' => '4', 'label' => '4 seconds'],
                                        ['value' => '8', 'label' => '8 seconds'],
                                        ['value' => '12', 'label' => '12 seconds'],
                                    ], '4', __('Select video duration (4, 8, or 12 seconds).')),
                                    self::selectInput('sora_size', __('Size'), [
                                        ['value' => '720x1280', 'label' => '720x1280'],
                                        ['value' => '1280x720', 'label' => '1280x720'],
                                        ['value' => '1024×1792', 'label' => '1024×1792'],
                                        ['value' => '1792×1024', 'label' => '1792×1024'],
                                    ], '720x1280'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'veo' => [
                'label'     => 'Generate video with Google VEO',
                'isActive'  => true,
                'subModels' => [
                    'veo2' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::VEO_2->value => [
                                'label'  => EntityEnum::VEO_2->label(),
                                'inputs' => [
                                    self::promptInput('Prompt'),
                                    self::aspectRatioSelect(AspectRatioEnum::class),
                                    self::durationSelect('5s', ['5s']),
                                    self::checkboxInput('enhance_prompt', __('Enhance Prompt'), true, __('Improves your prompt for higher-quality results')),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                    self::seedInput(),
                                    self::checkboxInput('auto_fix', __('Auto Fix Prompts'), true, __('Automatically rewrite prompts that fail content policy'), true),
                                ],
                            ],
                        ],
                    ],
                    'veo3' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::VEO_3->value => [
                                'label'  => EntityEnum::VEO_3->label(),
                                'inputs' => [
                                    self::promptInput('Prompt'),
                                    self::aspectRatioSelect(AspectRatioEnum::class),
                                    self::durationSelect('8s', ['4s', '6s', '8s']),
                                    self::checkboxInput('enhance_prompt', __('Enhance Prompt'), true, __('Improves your prompt for higher-quality results')),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                    self::seedInput(),
                                    self::checkboxInput('auto_fix', __('Auto Fix Prompts'), true, __('Automatically rewrite prompts that fail content policy'), true),
                                ],
                            ],
                            EntityEnum::VEO_3_FAST->value => [
                                'label'  => EntityEnum::VEO_3_FAST->label(),
                                'inputs' => [
                                    self::promptInput('Prompt'),
                                    self::aspectRatioSelect(AspectRatioEnum::class),
                                    self::durationSelect('8s', ['4s', '6s', '8s']),
                                    self::checkboxInput('enhance_prompt', __('Enhance Prompt'), true, __('Improves your prompt for higher-quality results')),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                    self::seedInput(),
                                    self::checkboxInput('auto_fix', __('Auto Fix Prompts'), true, __('Automatically rewrite prompts that fail content policy'), true),
                                ],
                            ],
                        ],
                    ],
                    'veo3.1' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::VEO_3_1_TEXT_TO_VIDEO->value => [
                                'label'  => EntityEnum::VEO_3_1_TEXT_TO_VIDEO->label(),
                                'inputs' => [
                                    self::promptInput('Prompt'),
                                    self::durationSelect('8s', ['4s', '6s', '8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square - will be outpainted)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                    self::seedInput(),
                                    self::negativePromptInput(),
                                    self::checkboxInput('enhance_prompt', __('Enhance Prompt'), true, __('Improves your prompt for higher-quality results'), true),
                                    self::checkboxInput('auto_fix', __('Auto Fix Prompts'), true, __('Automatically rewrite prompts that fail content policy'), true),
                                ],
                            ],
                            EntityEnum::VEO_3_1_TEXT_TO_VIDEO_FAST->value => [
                                'label'  => EntityEnum::VEO_3_1_TEXT_TO_VIDEO_FAST->label(),
                                'inputs' => [
                                    self::promptInput('Prompt'),
                                    self::durationSelect('8s', ['4s', '6s', '8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square - will be outpainted)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                    self::seedInput(),
                                    self::negativePromptInput(),
                                    self::checkboxInput('enhance_prompt', __('Enhance Prompt'), true, __('Improves your prompt for higher-quality results'), true),
                                    self::checkboxInput('auto_fix', __('Auto Fix Prompts'), true, __('Automatically rewrite prompts that fail content policy'), true),
                                ],
                            ],
                            EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value => [
                                'label'  => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'image_url', __('Upload Image for Image-to-Video (720p or higher)')),
                                    self::promptInput('Motion Description', 'Describe how the image should animate...', 3),
                                    self::durationSelect('8s', ['8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                ],
                            ],
                            EntityEnum::VEO_3_1_IMAGE_TO_VIDEO_FAST->value  => [
                                'label'  => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO_FAST->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'image_url', __('Upload Image for Image-to-Video (720p or higher)')),
                                    self::promptInput('Motion Description', 'Describe how the image should animate...', 3),
                                    self::durationSelect('8s', ['8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                ],
                            ],
                            EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO->value  => [
                                'label'  => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'first_frame_url', __('First Frame Image')),
                                    self::imageUpload(null, 'last_frame_url', __('Last Frame Image')),
                                    self::promptInput('Transition Description', 'Describe the transition between frames...', 3, false),
                                    self::durationSelect('8s', ['8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square - will be outpainted)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                ],
                            ],
                            EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO_FAST->value => [
                                'label'  => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO_FAST->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'first_frame_url', __('First Frame Image')),
                                    self::imageUpload(null, 'last_frame_url', __('Last Frame Image')),
                                    self::promptInput('Transition Description', 'Describe the transition between frames...', 3, false),
                                    self::durationSelect('8s', ['8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square - will be outpainted)'],
                                    ], '16:9'),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                ],
                            ],
                            EntityEnum::VEO_3_1_REFERENCE_TO_VIDEO->value => [
                                'label'  => EntityEnum::VEO_3_1_REFERENCE_TO_VIDEO->label(),
                                'inputs' => [
                                    self::multipleImageUpload('image_urls[]', __('Upload Reference Images (1-3 images to guide style/subject)')),
                                    self::promptInput('Video Style Description', 'Describe how to modify the reference video...'),
                                    self::durationSelect('8s', ['8s']),
                                    self::selectInput('resolution', __('Resolution'), [
                                        ['value' => '720p', 'label' => '720p'],
                                        ['value' => '1080p', 'label' => '1080p'],
                                    ], '720p', __('Video resolution quality')),
                                    self::checkboxInput('generate_audio', __('Generate Audio'), true, __('If disabled, video will be silent (saves 33% credits)')),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'luma-dream-machine' => [
                'label'     => 'Generate video with Luma Dream Machine',
                'isActive'  => true,
                'subModels' => [
                    'luma-dream-machine' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::LUMA_DREAM_MACHINE->value => [
                                'label'  => EntityEnum::LUMA_DREAM_MACHINE->label(),
                                'inputs' => [
                                    self::promptInput('Dream Prompt', 'Describe your dream video...'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'kling' => [
                'label'     => 'Generate video with Kling',
                'isActive'  => true,
                'subModels' => [
                    'kling-v1' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::KLING->value => [
                                'label'  => EntityEnum::KLING->label(),
                                'inputs' => [
                                    self::promptInput(),
                                ],
                            ],
                        ],
                    ],
                    'kling-v1.6' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::KLING_IMAGE->value => [
                                'label'  => EntityEnum::KLING_IMAGE->label(),
                                'inputs' => [
                                    self::imageUpload(),
                                    self::promptInput('Animation Description', 'Describe the animation...', 3),
                                ],
                            ],
                        ],
                    ],
                    'kling-v2.1' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::KLING_2_1->value => [
                                'label'  => EntityEnum::KLING_2_1->label(),
                                'inputs' => [
                                    self::imageUpload(),
                                    self::promptInput(),
                                ],
                            ],
                            EntityEnum::KLING_VIDEO->value => [
                                'label'  => EntityEnum::KLING_VIDEO->label(),
                                'inputs' => [
                                    self::videoUpload('video', 'Upload Video'),
                                    self::promptInput('Enhancement Instructions', 'Describe how to enhance the video...', 3, false),
                                ],
                            ],
                        ],
                    ],
                    'kling-v2.5' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::KLING_2_5_TURBO_PRO_TTV->value => [
                                'label'  => EntityEnum::KLING_2_5_TURBO_PRO_TTV->label(),
                                'inputs' => [
                                    self::promptInput('Video Description', 'Describe your video in detail...', 5),
                                    self::selectInput('kling25turbo_duration', __('Duration'), [
                                        ['value' => '5', 'label' => '5s'],
                                        ['value' => '10', 'label' => '10s'],
                                    ], '5', __('Video duration (5 or 10 seconds)')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square)'],
                                    ], '16:9'),
                                    self::selectInput('camera_movement', __('Camera Movement'), [
                                        ['value' => '', 'label' => __('None (Auto)')],
                                        ['value' => 'horizontal', 'label' => __('Horizontal')],
                                        ['value' => 'vertical', 'label' => __('Vertical')],
                                        ['value' => 'pan', 'label' => __('Pan')],
                                        ['value' => 'tilt', 'label' => __('Tilt')],
                                        ['value' => 'zoom', 'label' => __('Zoom')],
                                        ['value' => 'roll', 'label' => __('Roll')],
                                    ], '', __('Optional: Control camera movement in the generated video'), false),
                                    self::seedInput(0, 2147483647),
                                    self::negativePromptInput(),
                                    self::numberInput('cfg_scale', __('CFG Scale'), 0, 1, 0.1, '0.5', __('Guidance scale (0.0-1.0). Higher values follow prompt more closely. Default: 0.5'), false, true),
                                    self::checkboxInput('loop', __('Loop Video'), false, __('Make video seamlessly loop (only for 5s duration in Pro mode)'), true),
                                ],
                            ],
                            EntityEnum::KLING_2_5_TURBO_PRO_ITV->value => [
                                'label'  => EntityEnum::KLING_2_5_TURBO_PRO_ITV->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'image_url', __('Upload Image for Image-to-Video')),
                                    self::promptInput('Video Prompt', 'Describe the video transformation...', 4),
                                    self::selectInput('kling25turbo_duration', __('Duration'), [
                                        ['value' => '5', 'label' => '5s'],
                                        ['value' => '10', 'label' => '10s'],
                                    ], '5', __('Video duration (5 or 10 seconds)')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square)'],
                                    ], '16:9'),
                                    self::seedInput(0, 2147483647),
                                    self::negativePromptInput(),
                                    self::checkboxInput('loop', __('Loop Video'), false, __('Make video seamlessly loop (only for 5s duration in Pro mode)'), true),
                                ],
                            ],
                            EntityEnum::KLING_2_5_TURBO_STANDARD_ITV->value => [
                                'label'  => EntityEnum::KLING_2_5_TURBO_STANDARD_ITV->label(),
                                'inputs' => [
                                    self::imageUpload(null, 'image_url', __('Upload Image for Image-to-Video')),
                                    self::promptInput('Video Prompt', 'Describe the video transformation...', 4),
                                    self::selectInput('kling25turbo_duration', __('Duration'), [
                                        ['value' => '5', 'label' => '5s'],
                                        ['value' => '10', 'label' => '10s'],
                                    ], '5', __('Video duration (5 or 10 seconds)')),
                                    self::aspectRatioSelect([
                                        ['value' => '16:9', 'label' => '16:9 (Landscape)'],
                                        ['value' => '9:16', 'label' => '9:16 (Portrait)'],
                                        ['value' => '1:1', 'label' => '1:1 (Square)'],
                                    ], '16:9'),
                                    self::seedInput(0, 2147483647),
                                    self::negativePromptInput(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'minimax' => [
                'label'     => 'Generate video with Minimax',
                'isActive'  => true,
                'subModels' => [
                    'minimax' => [
                        'isActive' => true,
                        'features' => [
                            EntityEnum::MINIMAX->value => [
                                'label'  => EntityEnum::MINIMAX->label(),
                                'inputs' => [
                                    self::promptInput('Video Description', 'Enter your video prompt...'),
                                    self::negativePromptInput(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ============== GENERAL INPUT BUILDERS ==============

    private static function promptInput(
        string $label = 'Video Description',
        string $placeholder = 'Describe the video you want to generate...',
        int $rows = 4,
        bool $required = true
    ): array {
        return [
            'type'        => 'textarea',
            'name'        => 'prompt',
            'label'       => $label,
            'placeholder' => $placeholder,
            'required'    => $required,
            'rows'        => $rows,
        ];
    }

    private static function negativePromptInput(): array
    {
        return [
            'type'        => 'textarea',
            'name'        => 'negative_prompt',
            'label'       => __('Negative Prompt'),
            'placeholder' => __('What to avoid in the video...'),
            'required'    => false,
            'rows'        => 3,
            'advanced'    => true,
        ];
    }

    private static function selectInput(
        string $name,
        string $label,
        array $options,
        string $default,
        ?string $tooltip = null,
        bool $required = true
    ): array {
        $input = [
            'type'     => 'select',
            'name'     => $name,
            'label'    => $label,
            'required' => $required,
            'options'  => $options,
            'default'  => $default,
        ];

        if ($tooltip !== null) {
            $input['tooltip'] = $tooltip;
        }

        return $input;
    }

    private static function numberInput(
        string $name,
        string $label,
        ?float $min = null,
        ?float $max = null,
        ?float $step = null,
        ?string $placeholder = null,
        ?string $tooltip = null,
        bool $required = true,
        bool $advanced = false
    ): array {
        $input = [
            'type'     => 'number',
            'name'     => $name,
            'label'    => $label,
            'required' => $required,
        ];

        if ($min !== null) {
            $input['min'] = $min;
        }
        if ($max !== null) {
            $input['max'] = $max;
        }
        if ($step !== null) {
            $input['step'] = $step;
        }
        if ($placeholder !== null) {
            $input['placeholder'] = $placeholder;
        }
        if ($tooltip !== null) {
            $input['tooltip'] = $tooltip;
        }
        if ($advanced) {
            $input['advanced'] = true;
        }

        return $input;
    }

    private static function checkboxInput(
        string $name,
        string $label,
        bool $default = false,
        ?string $tooltip = null,
        bool $advanced = false
    ): array {
        $input = [
            'type'     => 'checkbox',
            'name'     => $name,
            'label'    => $label,
            'default'  => $default,
            'required' => false,
        ];

        if ($tooltip !== null) {
            $input['tooltip'] = $tooltip;
        }

        if ($advanced) {
            $input['advanced'] = true;
        }

        return $input;
    }

    private static function imageUpload(
        ?int $maxSize = null,
        string $name = 'image_url',
        string $label = 'Upload Image',
        bool $required = true
    ): array {
        $input = [
            'type'     => 'file',
            'name'     => $name,
            'label'    => $label,
            'accept'   => 'image/*',
            'required' => $required,
        ];

        if ($maxSize !== null) {
            $input['max_size'] = $maxSize;
        }

        return $input;
    }

    private static function multipleImageUpload(string $name, string $label): array
    {
        return [
            'type'      => 'file',
            'name'      => $name,
            'label'     => $label,
            'accept'    => 'image/*',
            'multiple'  => true,
            'required'  => true,
            'min_files' => 1,
            'max_files' => 3,
        ];
    }

    private static function videoUpload(string $name, string $label): array
    {
        return [
            'type'     => 'file',
            'name'     => $name,
            'label'    => $label,
            'accept'   => 'video/*',
            'required' => true,
        ];
    }

    private static function seedInput(?int $min = null, ?int $max = null): array
    {
        return self::numberInput(
            'seed',
            __('Seed'),
            $min,
            $max,
            null,
            __('Leave empty for random'),
            __('Optional: numeric seed for deterministic results'),
            false,
            true
        );
    }

    // ============== SPECIALIZED INPUT BUILDERS ==============

    private static function durationSelect(string $default, array $options): array
    {
        $formattedOptions = array_map(fn ($d) => [
            'value' => $d,
            'label' => $d,
        ], $options);

        $tooltip = count($options) === 1
            ? __('Video duration (8 seconds only for this mode)')
            : __('Video duration');

        return self::selectInput('duration', __('Duration'), $formattedOptions, $default, $tooltip);
    }

    private static function aspectRatioSelect(string|array $optionsOrEnum, ?string $default = null): array
    {
        // If enum class string is passed, build options from it
        if (is_string($optionsOrEnum) && class_exists($optionsOrEnum) && method_exists($optionsOrEnum, 'cases')) {
            $options = [];
            foreach ($optionsOrEnum::cases() as $ratio) {
                $options[] = ['value' => $ratio->value, 'label' => $ratio->label()];
            }
            $default = $default ?? '16:9';
        } else {
            // Direct options array passed
            $options = $optionsOrEnum;
            $default = $default ?? $options[0]['value'];
        }

        return self::selectInput(
            'aspect_ratio',
            __('Aspect Ratio'),
            $options,
            $default,
            __('Aspect ratio of the output video')
        );
    }
}
