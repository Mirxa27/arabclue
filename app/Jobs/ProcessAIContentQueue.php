<?php

namespace App\Jobs;

use App\Models\AIContentQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAIContentQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $queueItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AIContentQueue $queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = app(\App\Services\AI\AIService::class);
        $this->queueItem->markAsProcessing();
        try {
            $result = $service->generateContent($this->queueItem->content_type, $this->queueItem->parameters, $this->queueItem->context ?? []);
            $this->queueItem->markAsCompleted($result['content'], $result['tokens_used'] ?? null);
        } catch (\Exception $e) {
            $this->queueItem->markAsFailed($e->getMessage());
        }
    }
}
