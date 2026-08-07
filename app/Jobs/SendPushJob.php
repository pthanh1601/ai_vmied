<?php
namespace App\Jobs;

class SendPushJob
{
    protected $subscription;
    protected $message;

    public function __construct($subscription, $message)
    {
        $this->subscription = $subscription;
        $this->message = $message;
    }

    public function handle()
    {
        // App instance is globally available via app() helper
        // or we could inject it if job runner supports DI.
        // For simplicity, we use global app()
        if (app()->push->send($this->subscription, $this->message)) {
            // Success
            // In real app, maybe log success
        } else {
            throw new \Exception("Failed to send push notification");
        }
    }
}
