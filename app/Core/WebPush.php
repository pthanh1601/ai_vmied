<?php
namespace Neo\Core;

use Minishlink\WebPush\WebPush as PushClient;
use Minishlink\WebPush\Subscription;

class WebPush
{
    protected $webPush;
    protected $auth;

    public function __construct()
    {
        $this->auth = [
            'VAPID' => [
                'subject' => $_ENV['VAPID_SUBJECT'] ?? 'mailto:admin@example.com',
                'publicKey' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
                'privateKey' => $_ENV['VAPID_PRIVATE_KEY'] ?? '',
            ],
        ];

        // Check if keys are present
        if (!empty($this->auth['VAPID']['publicKey']) && !empty($this->auth['VAPID']['privateKey'])) {
            $this->webPush = new PushClient($this->auth);
        }
    }

    /**
     * Send Notification
     * @param array $subscription Subscription data from frontend (keys, endpoint)
     * @param string $payload Message
     * @return bool
     */
    public function send($subscription, $payload)
    {
        if (!$this->webPush)
            return false;

        $sub = Subscription::create($subscription);

        $report = $this->webPush->sendOneNotification($sub, $payload);

        return $report->isSuccess();
    }

    public function isConfigured()
    {
        return !empty($this->auth['VAPID']['publicKey']);
    }
}
