<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

class EmailController extends Controller
{
    protected $emailAnalyticsService;

    public function __construct(EmailAnalyticsService $emailAnalyticsService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->emailAnalyticsService = $emailAnalyticsService;
    }

    /**
     * Display email management dashboard
     */
    public function index(Request $request)
    {
        // Get email statistics
        $stats = $this->emailAnalyticsService->getEmailStats();

        // Get recent email activities
        $recentEmails = DB::table('jobs')
            ->where('queue', 'emails')
            ->orWhere('payload', 'like', '%mail%')
            ->latest()
            ->limit(10)
            ->get();

        // Get failed emails
        $failedEmails = DB::table('failed_jobs')
            ->where('payload', 'like', '%mail%')
            ->latest()
            ->limit(10)
            ->get();

        // Check email queue status
        $queueSize = Queue::size('emails');
        
        // Check SMTP configuration status
        $smtpStatus = $this->checkSmtpConfiguration();

        return view('admin.email.index', compact(
            'stats', 'recentEmails', 'failedEmails', 'queueSize', 'smtpStatus'
        ));
    }

    /**
     * Show SMTP configuration form
     */
    public function showSmtpConfig()
    {
        $config = [
            'mail_mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];

        $presets = [
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'outlook' => [
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'yahoo' => [
                'host' => 'smtp.mail.yahoo.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'mailgun' => [
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'sendgrid' => [
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
            ],
        ];

        return view('admin.email.smtp-config', compact('config', 'presets'));
    }

    /**
     * Update SMTP configuration
     */
    public function updateSmtpConfig(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|in:smtp,mailgun,ses,sendmail',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|in:tls,ssl,null',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        try {
            // Update .env file
            $this->updateEnvFile([
                'MAIL_MAILER' => $validated['mail_mailer'],
                'MAIL_HOST' => $validated['mail_host'],
                'MAIL_PORT' => $validated['mail_port'],
                'MAIL_USERNAME' => $validated['mail_username'],
                'MAIL_PASSWORD' => $validated['mail_password'],
                'MAIL_ENCRYPTION' => $validated['mail_encryption'] === 'null' ? null : $validated['mail_encryption'],
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
                'MAIL_FROM_NAME' => '"' . $validated['mail_from_name'] . '"',
            ]);

            // Clear config cache
            Artisan::call('config:clear');
            Artisan::call('config:cache');

            return redirect()->back()->with('success', 'SMTP configuration updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update SMTP configuration: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update SMTP configuration: ' . $e->getMessage());
        }
    }

    /**
     * Test SMTP configuration
     */
    public function testSmtp(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Temporarily update mail config for testing
            if ($request->has('temp_config')) {
                $tempConfig = $request->input('temp_config');
                
                Config::set('mail.default', $tempConfig['mail_mailer']);
                Config::set('mail.mailers.smtp.host', $tempConfig['mail_host']);
                Config::set('mail.mailers.smtp.port', $tempConfig['mail_port']);
                Config::set('mail.mailers.smtp.username', $tempConfig['mail_username']);
                Config::set('mail.mailers.smtp.password', $tempConfig['mail_password']);
                Config::set('mail.mailers.smtp.encryption', $tempConfig['mail_encryption']);
                Config::set('mail.from.address', $tempConfig['mail_from_address']);
                Config::set('mail.from.name', $tempConfig['mail_from_name']);
            }

            // Send test email
            Mail::raw('This is a test email from HabibiStay admin panel. If you receive this, your SMTP configuration is working correctly.', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                        ->subject('HabibiStay SMTP Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('SMTP test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'SMTP test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show email templates management
     */
    public function templates()
    {
        $templates = [
            'welcome' => [
                'name' => 'Welcome Email',
                'description' => 'Sent to new users after registration',
                'file' => 'emails.welcome',
                'variables' => ['user_name', 'verification_link', 'platform_name']
            ],
            'booking-confirmation' => [
                'name' => 'Booking Confirmation',
                'description' => 'Sent when a booking is confirmed',
                'file' => 'emails.booking-confirmation',
                'variables' => ['user_name', 'property_name', 'check_in', 'check_out', 'total_amount']
            ],
            'payment-confirmation' => [
                'name' => 'Payment Confirmation',
                'description' => 'Sent after successful payment',
                'file' => 'emails.payment-confirmation',
                'variables' => ['user_name', 'amount', 'transaction_id', 'booking_id']
            ],
            'host-payout' => [
                'name' => 'Host Payout Notification',
                'description' => 'Sent to hosts about payouts',
                'file' => 'emails.host-payout',
                'variables' => ['host_name', 'amount', 'payout_date', 'booking_reference']
            ],
            'review-request' => [
                'name' => 'Review Request',
                'description' => 'Sent to request reviews after checkout',
                'file' => 'emails.review-request',
                'variables' => ['user_name', 'property_name', 'review_link']
            ],
            'booking-reminder' => [
                'name' => 'Booking Reminder',
                'description' => 'Sent before check-in date',
                'file' => 'emails.booking-reminder',
                'variables' => ['user_name', 'property_name', 'check_in', 'host_contact']
            ],
            'system-maintenance' => [
                'name' => 'System Maintenance',
                'description' => 'Sent during system maintenance',
                'file' => 'emails.system-maintenance',
                'variables' => ['maintenance_start', 'maintenance_end', 'estimated_duration']
            ],
        ];

        return view('admin.email.templates', compact('templates'));
    }

    /**
     * Send bulk email
     */
    public function sendBulkEmail(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipients' => 'required|in:all_users,verified_users,hosts,guests,custom',
            'custom_emails' => 'required_if:recipients,custom|array',
            'custom_emails.*' => 'email',
            'send_immediately' => 'boolean',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        // Get recipients based on selection
        $recipients = $this->getEmailRecipients($validated['recipients'], $validated['custom_emails'] ?? []);

        if ($recipients->isEmpty()) {
            return redirect()->back()->with('error', 'No recipients found for the selected criteria.');
        }

        // Validate recipient count
        if ($recipients->count() > 1000) {
            return redirect()->back()->with('error', 'Cannot send to more than 1000 recipients at once.');
        }

        try {
            if ($validated['send_immediately']) {
                // Send immediately
                $this->dispatchBulkEmails($recipients, $validated['subject'], $validated['message']);
                $message = "Bulk email queued successfully for {$recipients->count()} recipients.";
            } else {
                // Schedule for later
                $scheduleAt = \Carbon\Carbon::parse($validated['schedule_at']);
                $this->scheduleBulkEmails($recipients, $validated['subject'], $validated['message'], $scheduleAt);
                $message = "Bulk email scheduled successfully for {$recipients->count()} recipients at {$scheduleAt->format('Y-m-d H:i')}.";
            }

            // Log the bulk email
            Log::info('Bulk email sent', [
                'admin_id' => auth()->id(),
                'recipients_count' => $recipients->count(),
                'subject' => $validated['subject'],
                'scheduled' => !$validated['send_immediately'],
                'schedule_at' => $validated['schedule_at'] ?? null,
            ]);

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Bulk email failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send bulk email: ' . $e->getMessage());
        }
    }

    /**
     * Show email analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->input('period', '30'); // days
        $analytics = $this->emailAnalyticsService->getAnalytics($period);

        return view('admin.email.analytics', compact('analytics', 'period'));
    }

    /**
     * Show email queue management
     */
    public function queue()
    {
        $queueStats = [
            'pending' => Queue::size('emails'),
            'failed' => DB::table('failed_jobs')->where('queue', 'emails')->count(),
            'completed' => Cache::get('email_queue_completed_today', 0),
        ];

        $recentJobs = DB::table('jobs')
            ->where('queue', 'emails')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $failedJobs = DB::table('failed_jobs')
            ->where('queue', 'emails')
            ->orderBy('failed_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.email.queue', compact('queueStats', 'recentJobs', 'failedJobs'));
    }

    /**
     * Retry failed email jobs
     */
    public function retryFailedJobs(Request $request)
    {
        $validated = $request->validate([
            'job_ids' => 'required|array',
            'job_ids.*' => 'integer',
        ]);

        try {
            foreach ($validated['job_ids'] as $jobId) {
                Artisan::call('queue:retry', ['id' => $jobId]);
            }

            return redirect()->back()->with('success', 'Failed jobs have been queued for retry.');
        } catch (\Exception $e) {
            Log::error('Failed to retry email jobs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to retry jobs: ' . $e->getMessage());
        }
    }

    /**
     * Clear email queue
     */
    public function clearQueue(Request $request)
    {
        $validated = $request->validate([
            'queue_type' => 'required|in:pending,failed,all',
        ]);

        try {
            switch ($validated['queue_type']) {
                case 'pending':
                    Artisan::call('queue:clear', ['--queue' => 'emails']);
                    $message = 'Pending email queue cleared successfully.';
                    break;
                    
                case 'failed':
                    Artisan::call('queue:flush');
                    $message = 'Failed email queue cleared successfully.';
                    break;
                    
                case 'all':
                    Artisan::call('queue:clear', ['--queue' => 'emails']);
                    Artisan::call('queue:flush');
                    $message = 'All email queues cleared successfully.';
                    break;
            }

            Log::info('Email queue cleared', [
                'admin_id' => auth()->id(),
                'queue_type' => $validated['queue_type'],
            ]);

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to clear email queue: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to clear queue: ' . $e->getMessage());
        }
    }

    /**
     * Preview email template
     */
    public function previewTemplate(Request $request, $template)
    {
        $validated = $request->validate([
            'data' => 'array',
        ]);

        try {
            $sampleData = $this->getSampleTemplateData($template);
            $data = array_merge($sampleData, $validated['data'] ?? []);

            return view("emails.{$template}", $data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Template not found or invalid: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get email recipients based on criteria
     */
    private function getEmailRecipients(string $criteria, array $customEmails = [])
    {
        switch ($criteria) {
            case 'all_users':
                return User::active()->select('id', 'name', 'email')->get();
                
            case 'verified_users':
                return User::active()->verified()->select('id', 'name', 'email')->get();
                
            case 'hosts':
                return User::active()->where('role', 'host')->select('id', 'name', 'email')->get();
                
            case 'guests':
                return User::active()->where('role', 'guest')->select('id', 'name', 'email')->get();
                
            case 'custom':
                return collect($customEmails)->map(function ($email) {
                    return (object) ['email' => $email, 'name' => 'User'];
                });
                
            default:
                return collect();
        }
    }

    /**
     * Dispatch bulk emails to queue
     */
    private function dispatchBulkEmails($recipients, string $subject, string $message)
    {
        foreach ($recipients as $recipient) {
            \App\Jobs\SendBulkEmail::dispatch($recipient, $subject, $message)
                ->onQueue('emails');
        }
    }

    /**
     * Schedule bulk emails
     */
    private function scheduleBulkEmails($recipients, string $subject, string $message, $scheduleAt)
    {
        foreach ($recipients as $recipient) {
            \App\Jobs\SendBulkEmail::dispatch($recipient, $subject, $message)
                ->delay($scheduleAt)
                ->onQueue('emails');
        }
    }

    /**
     * Check SMTP configuration status
     */
    private function checkSmtpConfiguration(): array
    {
        $required = ['mail.mailers.smtp.host', 'mail.mailers.smtp.username', 'mail.from.address'];
        $status = [
            'configured' => true,
            'missing' => [],
        ];

        foreach ($required as $key) {
            if (empty(config($key))) {
                $status['configured'] = false;
                $status['missing'][] = $key;
            }
        }

        return $status;
    }

    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $value = is_null($value) ? '' : $value;
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Get sample data for template preview
     */
    private function getSampleTemplateData(string $template): array
    {
        $defaultData = [
            'user_name' => 'John Doe',
            'platform_name' => 'HabibiStay',
        ];

        $templateData = [
            'welcome' => [
                'verification_link' => url('/verify-email/sample'),
            ],
            'booking-confirmation' => [
                'property_name' => 'Luxury Villa in Dubai',
                'check_in' => '2024-07-15',
                'check_out' => '2024-07-20',
                'total_amount' => 'SAR 2,500',
            ],
            'payment-confirmation' => [
                'amount' => 'SAR 2,500',
                'transaction_id' => 'TXN123456789',
                'booking_id' => 'BK123456',
            ],
            'host-payout' => [
                'host_name' => 'Ahmed Al-Rashid',
                'amount' => 'SAR 2,125',
                'payout_date' => '2024-07-25',
                'booking_reference' => 'BK123456',
            ],
            'review-request' => [
                'property_name' => 'Luxury Villa in Dubai',
                'review_link' => url('/review/sample'),
            ],
            'booking-reminder' => [
                'property_name' => 'Luxury Villa in Dubai',
                'check_in' => '2024-07-15 15:00',
                'host_contact' => '+966 50 123 4567',
            ],
            'system-maintenance' => [
                'maintenance_start' => '2024-07-20 02:00 UTC',
                'maintenance_end' => '2024-07-20 06:00 UTC',
                'estimated_duration' => '4 hours',
            ],
        ];

        return array_merge($defaultData, $templateData[$template] ?? []);
    }
}