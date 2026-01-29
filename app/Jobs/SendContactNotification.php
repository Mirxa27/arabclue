<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * SendContactNotification Job - Email Notification Dispatcher
 * 
 * Handles asynchronous sending of contact form notifications
 * with retry logic and error handling
 */
class SendContactNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Contact form data
     */
    protected array $contactData;

    /**
     * Job retry attempts
     */
    public int $tries = 3;

    /**
     * Job timeout in seconds
     */
    public int $timeout = 120;

    /**
     * Create a new job instance
     */
    public function __construct(array $contactData)
    {
        $this->contactData = $contactData;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        try {
            // Send notification to admin team
            $this->sendAdminNotification();
            
            // Send confirmation to user
            $this->sendUserConfirmation();
            
            Log::info('Contact notification sent successfully', [
                'contact_email' => $this->contactData['email'],
                'subject' => $this->contactData['subject']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send contact notification', [
                'contact_data' => $this->contactData,
                'error' => $e->getMessage()
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Send notification to admin team
     */
    protected function sendAdminNotification(): void
    {
        $adminEmails = config('mail.admin_emails', ['admin@habibistay.com']);
        
        foreach ($adminEmails as $adminEmail) {
            Mail::send('emails.contact.admin-notification', [
                'contactData' => $this->contactData
            ], function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                        ->subject('New Contact Form Submission - ' . $this->contactData['subject'])
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }
    }

    /**
     * Send confirmation to user
     */
    protected function sendUserConfirmation(): void
    {
        Mail::send('emails.contact.user-confirmation', [
            'contactData' => $this->contactData
        ], function ($message) {
            $message->to($this->contactData['email'], $this->contactData['name'])
                    ->subject('We received your message - HabibiStay')
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Contact notification job failed after all retries', [
            'contact_data' => $this->contactData,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
        
        // Optionally send alert to developers
        $this->sendFailureAlert($exception);
    }

    /**
     * Send failure alert to developers
     */
    protected function sendFailureAlert(\Throwable $exception): void
    {
        try {
            Mail::raw(
                "Contact notification job failed:\n\n" .
                "Contact: {$this->contactData['email']}\n" .
                "Subject: {$this->contactData['subject']}\n" .
                "Error: {$exception->getMessage()}\n" .
                "Attempts: {$this->attempts()}",
                function ($message) {
                    $message->to(config('mail.developer_email', 'dev@habibistay.com'))
                            ->subject('[URGENT] Contact Notification Job Failed')
                            ->from(config('mail.from.address'), config('mail.from.name'));
                }
            );
        } catch (\Exception $e) {
            // Last resort logging if email also fails
            Log::emergency('Failed to send job failure alert', [
                'original_error' => $exception->getMessage(),
                'alert_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags for the job
     */
    public function tags(): array
    {
        return [
            'contact-notification',
            'email:' . $this->contactData['email'],
            'type:' . $this->contactData['interested_in']
        ];
    }
}