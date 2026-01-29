<?php

namespace App\Extensions\AiVideoPro\System\Http\Controllers;

use App\Domains\Engine\Services\FalAIService;
use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Extensions\AiVideoPro\System\Http\Requests\StoreAiVideoProRequest;
use App\Extensions\AiVideoPro\System\Models\UserFall;
use App\Extensions\AiVideoPro\System\Services\ModelConfigurationService;
use App\Extensions\AiVideoPro\System\Services\SoraService;
use App\Helpers\Classes\ApiHelper;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Packages\FalAI\FalAIService as PackageFalAIService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AiVideoProController extends Controller
{
    private const UPLOAD_DISK = 'public';

    private const RANDOM_STRING_LENGTH = 12;

    private PackageFalAIService $falAIService;

    public function __construct()
    {
        $this->falAIService = new PackageFalAIService(ApiHelper::setFalAIKey());
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $list = UserFall::query()->where('user_id', auth()->user()->id)->get()->toArray();

        $inProgress = collect($list)->filter(function ($entry) {
            return in_array($entry['status'], ['IN_QUEUE', 'queued']);
        })->pluck('id')->toArray();

        $models = ModelConfigurationService::getConfig();

        return view('ai-video-pro::index', compact(['list', 'inProgress', 'models']));
    }

    public function delete(string $id): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with(['message' => 'This feature is disabled in demo mode.', 'type' => 'error']);
        }

        $model = UserFall::query()->findOrFail($id);

        $model->delete();

        return back()->with(['message' => 'Deleted Successfully.', 'type' => 'success']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAiVideoProRequest $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return $this->errorResponse(__('This feature is disabled in demo mode.'));
        }

        if (! ApiHelper::setFalAIKey()) {
            return $this->errorResponse(__('Please set FAL AI key.'));
        }

        $validated = $request->validated();
        $entityEnum = EntityEnum::fromSlug($validated['feature']);

        try {
            $driver = $this->calculateCredits($validated['action'], $entityEnum, $validated);
            $driver->redirectIfNoCreditBalance();
        } catch (Exception $e) {
            Log::error('Credit calculation failed', [
                'error'   => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse($e->getMessage());
        }

        try {
            return $this->processGeneration($validated, $entityEnum, $driver);
        } catch (Exception $e) {
            Log::error('Video generation failed', [
                'error'   => $e->getMessage(),
                'action'  => $validated['action'],
                'feature' => $validated['feature'],
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse(__('Generation failed: ') . $e->getMessage());
        }
    }

    private function processGeneration(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $handlers = [
            'sora'               => 'handleSora',
            'veo'                => 'handleVeo',
            'kling'              => 'handleKling',
            'luma-dream-machine' => 'handleLumaDreamMachine',
            'minimax'            => 'handleMinimax',
        ];

        $action = $validated['action'];

        if (! isset($handlers[$action])) {
            return $this->errorResponse(__('Invalid AI model selected.'));
        }

        return $this->{$handlers[$action]}($validated, $entityEnum, $driver);
    }

    private function calculateCredits(string $action, EntityEnum $entityEnum, array $validated)
    {
        if ($action === 'sora') {
            $seconds = (int) ($validated['sora_seconds'] ?? 4);

            return Entity::driver($entityEnum)->inputSecond($seconds)->calculateCredit();
        }

        return Entity::driver($entityEnum)->inputVideoCount(1)->calculateCredit();
    }

    private function handleSora(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        // Don't upload yet - pass the UploadedFile object directly
        $imageFile = $validated['image_url'] ?? null;

        $params = [
            'prompt'    => $validated['prompt'],
            'model'     => $entityEnum->value,
            'seconds'   => (int) ($validated['sora_seconds'] ?? 4),
            'size'      => $validated['sora_size'] ?? '720x1280',
            'image_url' => $imageFile, // Pass the UploadedFile object, not the uploaded path
        ];

        $response = SoraService::generate($params);

        if ($this->isErrorResponse($response)) {
            return $this->errorResponse(
                $response['error']['message'] ?? $response['message'] ?? __('Generation Failed')
            );
        }

        // Only upload for database record after successful generation
        $uploadedImageUrl = $imageFile ? $this->uploadSingleFile($validated, 'image_url', true) : null;

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $entityEnum->value,
            $response,
            $uploadedImageUrl
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Created Successfully.'));
    }

    private function handleVeo(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $legacyFeatures = ['veo2'];

        if (in_array($validated['feature'], $legacyFeatures, true)) {
            return $this->handleLegacyVeo($validated, $entityEnum, $driver);
        }

        $payload = $this->buildVeoPayload($validated);
        $response = $this->falAIService->textToVideoModel($entityEnum)->submit($payload);
        $resData = $response->getData();

        if (isset($resData->status) && $resData->status === 'error') {
            return $this->errorResponse($resData->message ?? __('VEO generation failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            (array) $resData->resData
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Video generation started successfully.'));
    }

    private function handleLegacyVeo(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $payload = $this->buildVeoPayload($validated);
        $response = FalAIService::veo2Generate($payload['prompt']);
        if ($response->failed()) {
            return back()->with([
                'message' => $response->status() . ' ' . $response->reason() . ': ' .
                    $response->json('detail', __('Unknown error occurred')),
                'type' => 'error',
            ]);
        }
        $jsonRes = $response->json();
        if (isset($jsonRes['status']) && $jsonRes['status'] === 'error') {
            return back()->with(['message' => $jsonRes['message'], 'type' => 'error']);
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            (array) $jsonRes
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Video generation started successfully.'));
    }

    private function handleKling(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $legacyFeatures = ['kling', 'klingImage', 'klingV21', 'kling-video'];
        $kling26ProFeatures = [
            EntityEnum::KLING_2_6_PRO_TTV->value,
            EntityEnum::KLING_2_6_PRO_ITV->value,
        ];

        if (in_array($validated['feature'], $legacyFeatures, true)) {
            return $this->handleLegacyKling($validated, $entityEnum, $driver);
        }

        if (in_array($validated['feature'], $kling26ProFeatures, true)) {
            return $this->handleKling26Pro($validated, $entityEnum, $driver);
        }

        return $this->handleKling25($validated, $entityEnum, $driver);
    }

    private function handleLegacyKling(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $feature = $validated['feature'];
        $url = null;

        if (in_array($feature, ['klingImage', 'klingV21'], true)) {
            $url = $this->uploadSingleFile($validated, 'image_url', true);
        } elseif ($feature === 'kling-video') {
            $url = $this->uploadSingleFile($validated, 'video', true);
        }

        $method = $feature . 'Generate';
        if (! method_exists(FalAIService::class, $method)) {
            return $this->errorResponse(__('Invalid Kling method.'));
        }

        $response = FalAIService::$method($validated['prompt'], $url);

        if ($this->isErrorResponse($response)) {
            return $this->errorResponse($response['message'] ?? __('Generation Failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $feature,
            $response,
            $url
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Created Successfully.'));
    }

    private function handleKling25(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $payload = $this->buildKling25Payload($validated);

        $response = $this->falAIService->textToVideoModel($entityEnum)->submit($payload);
        $resData = $response->getData();

        if (isset($resData->status) && $resData->status === 'error') {
            return $this->errorResponse($resData->message ?? __('Kling generation failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            (array) $resData->resData,
            $payload['image_url'] ?? null
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Kling video generation started successfully.'));
    }

    private function handleKling26Pro(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $payload = $this->buildKling26ProPayload($validated);

        $response = $this->falAIService->textToVideoModel($entityEnum)->submit($payload);
        $resData = $response->getData();

        if (isset($resData->status) && $resData->status === 'error') {
            return $this->errorResponse($resData->message ?? __('Kling generation failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            (array) $resData->resData,
            $payload['image_url'] ?? null
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Kling video generation started successfully.'));
    }

    private function handleLumaDreamMachine(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $response = FalAIService::minimaxGenerate($validated['prompt']);

        if ($this->isErrorResponse($response)) {
            return $this->errorResponse($response['message'] ?? __('Generation Failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            $response
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Created Successfully.'));
    }

    private function handleMinimax(array $validated, EntityEnum $entityEnum, $driver): RedirectResponse
    {
        $response = FalAIService::minimaxGenerate($validated['prompt']);

        if ($this->isErrorResponse($response)) {
            return $this->errorResponse($response['message'] ?? __('Generation Failed'));
        }

        $this->createUserFall(
            auth()->id(),
            $validated['prompt'],
            $validated['feature'],
            $response
        );

        $driver->decreaseCredit();

        return $this->successResponse(__('Created Successfully.'));
    }

    // ============== PAYLOAD BUILDERS ==============

    private function buildVeoPayload(array $validated): array
    {
        $payload = ['prompt' => $validated['prompt']];

        // Add simple fields
        $simpleFields = ['duration', 'resolution', 'aspect_ratio'];
        foreach ($simpleFields as $field) {
            if (isset($validated[$field])) {
                $payload[$field] = $validated[$field];
            }
        }

        if (isset($validated['feature']) && str_starts_with($validated['feature'], 'veo3.1/')) {
            $payload['mode'] = $validated['feature'];
        }

        // Handle boolean fields
        if (isset($validated['generate_audio'])) {
            $payload['generate_audio'] = filter_var($validated['generate_audio'], FILTER_VALIDATE_BOOLEAN);
        }

        // Handle file uploads
        $payload = $this->addVeoFileUploads($payload, $validated);

        // Add advanced options
        $payload = $this->addAdvancedOptions($payload, $validated);

        return $this->removeEmptyValues($payload);
    }

    private function addVeoFileUploads(array $payload, array $validated): array
    {
        $feature = $validated['feature'];

        if (str_contains($feature, 'image-to-video') && ! str_contains($feature, 'first-last-frame')) {
            $payload['image_url'] = $this->uploadSingleFile($validated, 'image_url', true);
        } elseif (str_contains($feature, 'first-last-frame')) {
            $payload['first_frame_url'] = $this->uploadSingleFile($validated, 'first_frame_url', true);
            $payload['last_frame_url'] = $this->uploadSingleFile($validated, 'last_frame_url', true);
        } elseif (str_contains($feature, 'reference-to-video')) {
            $payload['image_urls'] = $this->uploadMultipleFiles($validated, 'image_urls');
        }

        return $payload;
    }

    private function addAdvancedOptions(array $payload, array $validated): array
    {
        $booleanFields = ['enhance_prompt', 'auto_fix'];
        foreach ($booleanFields as $field) {
            if (isset($validated[$field])) {
                $payload[$field] = filter_var($validated[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (isset($validated['seed']) && $validated['seed'] !== '') {
            $payload['seed'] = (int) $validated['seed'];
        }

        if (! empty($validated['negative_prompt'])) {
            $payload['negative_prompt'] = $validated['negative_prompt'];
        }

        return $payload;
    }

    private function buildKling25Payload(array $validated): array
    {
        $payload = [
            'prompt'       => $validated['prompt'],
            'duration'     => (int) ($validated['kling25turbo_duration'] ?? 5),
            'aspect_ratio' => $validated['kling25turbo_aspect_ratio'] ?? '16:9',
        ];

        $feature = $validated['feature'];

        // Handle image upload for image-to-video modes
        if (isset($validated['image_url']) && str_contains($feature, 'image-to-video')) {
            $payload['image_url'] = $this->uploadSingleFile($validated, 'image_url', true);
        }

        // Add Pro TTV specific parameters
        if ($feature === EntityEnum::KLING_2_5_TURBO_PRO_TTV->value) {
            if (! empty($validated['camera_movement'])) {
                $payload['camera_movement'] = ['type' => $validated['camera_movement']];
            }
            if (isset($validated['cfg_scale'])) {
                $payload['cfg_scale'] = (float) $validated['cfg_scale'];
            }
        }

        // Add optional parameters
        if (isset($validated['seed']) && $validated['seed'] !== null) {
            $payload['seed'] = (int) $validated['seed'];
        }

        if (! empty($validated['negative_prompt'])) {
            $payload['negative_prompt'] = $validated['negative_prompt'];
        }

        // Add loop parameter for Pro modes
        if (isset($validated['loop']) && str_contains($feature, 'pro')) {
            $payload['loop'] = filter_var($validated['loop'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->removeEmptyValues($payload);
    }

    private function buildKling26ProPayload(array $validated): array
    {
        $payload = [
            'prompt'       => $validated['prompt'],
            'duration'     => (int) ($validated['kling26pro_duration'] ?? 5),
            'aspect_ratio' => $validated['kling26pro_aspect_ratio'] ?? '16:9',
        ];

        $feature = $validated['feature'];

        // Handle image upload for image-to-video modes
        if (isset($validated['image_url']) && str_contains($feature, 'image-to-video')) {
            $payload['image_url'] = $this->uploadSingleFile($validated, 'image_url', true);
        }

        if (isset($validated['cfg_scale'])) {
            $payload['cfg_scale'] = (float) $validated['cfg_scale'];
        }

        if (! empty($validated['negative_prompt'])) {
            $payload['negative_prompt'] = $validated['negative_prompt'];
        }

        return $this->removeEmptyValues($payload);
    }
    // ============== FILE UPLOAD HELPERS ==============

    private function uploadSingleFile(array $validated, string $fieldName, bool $returnUrl = false): ?string
    {
        if (! isset($validated[$fieldName]) || ! ($validated[$fieldName] instanceof UploadedFile)) {
            return null;
        }

        $file = $validated[$fieldName];

        try {
            $fileName = $this->generateUniqueFileName($file);
            $content = file_get_contents($file->getRealPath());

            Storage::disk(self::UPLOAD_DISK)->put($fileName, $content);

            if ($returnUrl) {
                return Helper::parseUrl(config('app.url') . '/uploads/' . $fileName);
            }

            return $fileName;
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'field' => $fieldName,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(__('File upload failed: ') . $e->getMessage());
        }
    }

    private function uploadMultipleFiles(array $validated, string $fieldName): array
    {
        if (! isset($validated[$fieldName]) || ! is_array($validated[$fieldName])) {
            return [];
        }

        $urls = [];
        foreach ($validated[$fieldName] as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $fileName = $this->generateUniqueFileName($file);
                    $content = file_get_contents($file->getRealPath());

                    Storage::disk(self::UPLOAD_DISK)->put($fileName, $content);
                    $urls[] = Helper::parseUrl(config('app.url') . '/uploads/' . $fileName);
                } catch (Exception $e) {
                    Log::warning('Multiple file upload - single file failed', [
                        'field' => $fieldName,
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }
            }
        }

        return $urls;
    }

    private function generateUniqueFileName(UploadedFile $file): string
    {
        return Str::random(self::RANDOM_STRING_LENGTH) . '.' . $file->guessExtension();
    }

    private function isErrorResponse($response): bool
    {
        if (! is_array($response)) {
            return false;
        }

        return isset($response['error']) ||
            (isset($response['status']) && $response['status'] === 'error') ||
            (isset($response['status']) && $response['status'] === 'failed');
    }

    private function removeEmptyValues(array $data): array
    {
        return array_filter($data, static function ($value) {
            if (is_null($value) || $value === '') {
                return false;
            }
            if (is_array($value) && empty($value)) {
                return false;
            }

            return true;
        });
    }

    private function successResponse(string $message): RedirectResponse
    {
        return back()->with([
            'message' => $message,
            'type'    => 'success',
        ]);
    }

    private function errorResponse(string $message): RedirectResponse
    {
        return back()->with([
            'message' => $message,
            'type'    => 'error',
        ]);
    }

    private function createUserFall(
        int $userId,
        string $prompt,
        string $action,
        array $response,
        ?string $imageUrl = null
    ): void {
        UserFall::create([
            'user_id'          => $userId,
            'prompt'           => $prompt,
            'prompt_image_url' => $imageUrl,
            'status'           => $response['status'] ?? 'IN_QUEUE',
            'request_id'       => $response['request_id'] ?? $response['id'] ?? null,
            'response_url'     => $response['response_url'] ?? null,
            'model'            => $action,
        ]);
    }

    public function checkVideoStatus(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $ids = (array) $request->get('ids', []);

        $entries = UserFall::where('user_id', $userId)
            ->where('status', '!=', 'complete')
            ->whereIn('id', $ids)
            ->get();

        if ($entries->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // Delete error entries in one go
        $errorIds = $entries->where('status', 'error')->pluck('id');
        if ($errorIds->isNotEmpty()) {
            UserFall::whereIn('id', $errorIds)->delete();
            $entries = $entries->reject(fn ($e) => $errorIds->contains($e->id));
        }

        $data = collect();

        foreach ($entries as $entry) {
            $result = match (true) {
                str_starts_with($entry->model ?? '', 'sora')                  => $this->handleSoraEntry($entry),
                str_starts_with($entry->model ?? '', 'veo3.1/')               => $this->handleVeoEntry($entry),
                str_starts_with($entry->model ?? '', 'kling-2.5-turbo/')      => $this->handleKling25Entry($entry),
                str_starts_with($entry->model ?? '', 'kling-video/v2.6/pro/') => $this->handleKling26ProEntry($entry),
                default                                                       => $this->handleFalEntry($entry),
            };

            if ($result) {
                $data->push($result);
            }
        }

        return response()->json(['data' => $data]);
    }

    private function handleSoraEntry($entry): ?array
    {
        $response = SoraService::getStatus($entry->request_id);
        $status = $response['status'] ?? null;

        if ($status === 'completed') {
            $vidUrl = SoraService::getVideo($entry->request_id);
            if ($vidUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $vidUrl]);

                return $this->renderVideoItem($entry, $vidUrl);
            }
            $entry->delete();
        }

        if ($status === 'failed') {
            $entry->delete();
        }

        return null;
    }

    private function handleVeoEntry($entry): ?array
    {
        // Extract mode from model string (e.g., "veo3.1/text-to-video-fast" -> "text-to-video-fast")
        $mode = str_replace('veo3.1/', '', $entry->model);
        $entity = $this->detectVeo31EntityEnum($mode);
        $check = $this->falAIService->textToVideoModel($entity)->checkStatus($entry->request_id)->getData();
        $status = $check->resData->status ?? null;

        if ($status === 'NOT_FOUND') {
            $entry->delete();

            return null;
        }

        if ($status !== 'COMPLETED') {
            return null;
        }

        $result = $this->falAIService->textToVideoModel($entity)->getResult($entry->request_id)->getData();

        if (($result->status ?? null) === 'success') {
            $videoUrl = $result->resData->video->url ?? null;
            if ($videoUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $videoUrl]);

                return $this->renderVideoItem($entry, $videoUrl);
            }
        }

        if (in_array(($result->status ?? null), ['failed', 'error'])) {
            $entry->delete();
        }

        return null;
    }

    private function handleKling25Entry($entry): ?array
    {
        // Extract mode from model string (e.g., "kling-2.5-turbo/text-to-video" -> "text-to-video")
        $mode = str_replace('kling-2.5-turbo/', '', $entry->model);
        $entity = $this->detectKling25EntityEnum($mode);

        $check = $this->falAIService->textToVideoModel($entity)->checkStatus($entry->request_id)->getData();
        $status = $check->resData->status ?? null;

        if ($status !== 'COMPLETED') {
            return null;
        }

        $result = $this->falAIService->textToVideoModel($entity)->getResult($entry->request_id)->getData();

        if (($result->status ?? null) === 'success') {
            $videoUrl = $result->resData->video->url ?? null;
            if ($videoUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $videoUrl]);

                return $this->renderVideoItem($entry, $videoUrl);
            }
        }

        if (in_array(($result->status ?? null), ['failed', 'error'])) {
            $entry->delete();
        }

        return null;
    }

    private function handleKling26ProEntry($entry): ?array
    {
        // Extract mode from model string (e.g., "kling-2.5-turbo/text-to-video" -> "text-to-video")
        $mode = str_replace('kling-video/v2.6/pro/', '', $entry->model);
        $entity = $this->detectKling26ProEntityEnum($mode);

        $check = $this->falAIService->textToVideoModel($entity)->checkStatus($entry->request_id)->getData();
        $status = $check->resData->status ?? null;

        if ($status !== 'COMPLETED') {
            return null;
        }

        $result = $this->falAIService->textToVideoModel($entity)->getResult($entry->request_id)->getData();

        if (($result->status ?? null) === 'success') {
            $videoUrl = $result->resData->video->url ?? null;
            if ($videoUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $videoUrl]);

                return $this->renderVideoItem($entry, $videoUrl);
            }
        }

        if (in_array(($result->status ?? null), ['failed', 'error'])) {
            $entry->delete();
        }

        return null;
    }

    private function handleFalEntry($entry): ?array
    {
        $response = FalAIService::getStatus($entry->response_url);

        if (! empty($response['video']['url'])) {
            $url = $response['video']['url'];
            $entry->update(['status' => 'complete', 'video_url' => $url]);

            return $this->renderVideoItem($entry, $url);
        }

        $detail = $response['detail'] ?? null;

        // Handle failed or invalid responses
        if (
            in_array($detail, [
                'Internal Server Error',
                'Luma API timed out',
                "Luma API failed: generation.state='failed' generation.failure_reason='400: prompt not allowed because advanced moderation failed'",
            ]) ||
            (isset($detail[0]['type']) && $detail[0]['type'] === 'image_load_error')
        ) {
            $entry->delete();
        }

        return null;
    }

    private function renderVideoItem($entry, string $url): array
    {
        $entry->video_url = $url;
        $entry->status = 'complete';

        return [
            'divId' => "video-{$entry->id}",
            'html'  => view('ai-video-pro::partials.video-item', ['entry' => $entry])->render(),
        ];
    }

    private function detectKling25EntityEnum(?string $mode): EntityEnum
    {
        return match ($mode) {
            'image-to-video'     => EntityEnum::KLING_2_5_TURBO_STANDARD_ITV,
            'image-to-video-pro' => EntityEnum::KLING_2_5_TURBO_PRO_ITV,
            default              => EntityEnum::KLING_2_5_TURBO_PRO_TTV, // text-to-video as default
        };
    }

    private function detectKling26ProEntityEnum(?string $mode): EntityEnum
    {
        return match ($mode) {
            'image-to-video' => EntityEnum::KLING_2_6_PRO_ITV,
            default          => EntityEnum::KLING_2_6_PRO_TTV, // text-to-video as default
        };
    }

    private function detectVeo31EntityEnum(?string $mode): EntityEnum
    {
        return match ($mode) {
            'image-to-video'                 => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO,
            'image-to-video-fast'            => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO_FAST,
            'first-last-frame-to-video'      => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO,
            'first-last-frame-to-video-fast' => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO_FAST,
            'reference-to-video'             => EntityEnum::VEO_3_1_REFERENCE_TO_VIDEO,
            'text-to-video-fast'             => EntityEnum::VEO_3_1_TEXT_TO_VIDEO_FAST,
            default                          => EntityEnum::VEO_3_1_TEXT_TO_VIDEO,
        };
    }
}
