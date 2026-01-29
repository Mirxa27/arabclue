<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recipient;
    public $subject;
    public $message;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($recipient, string $subject, string $message)
    {
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::send([], [], function ($mail) {
                $mail->to($this->recipient->email, $this->recipient->name ?? 'User')
                     ->subject($this->subject)
                     ->html($this->message);
            });

            Log::info('Bulk email sent successfully', [
                'recipient' => $this->recipient->email,
                'subject' => $this->subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk email failed', [
                'recipient' => $this->recipient->email,
                'subject' => $this->subject,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk email job failed permanently', [
            'recipient' => $this->recipient->email,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}