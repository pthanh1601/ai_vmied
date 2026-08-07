<?php
namespace App\Middleware;

use Neo\Core\App;

class ThrottleMiddleware
{
    public function handle(App $app)
    {
        // Simple IP-based Rate Limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'];

        // Key specific to IP and Endpoint (or just IP globally if preferred)
        $key = 'throttle_' . md5($ip . $uri);
        $limit = 60; // Requests
        $minutes = 1; // Time Window

        $data = $app->cache->get($key);

        if (!$data) {
            $data = ['hits' => 1, 'reset_at' => time() + ($minutes * 60)];
            $app->cache->put($key, $data, $minutes);
        } else {
            // Check if window expired (FileCache handles expiration mostly, but let's be safe)
            if (time() > $data['reset_at']) {
                $data = ['hits' => 1, 'reset_at' => time() + ($minutes * 60)];
            } else {
                $data['hits']++;
            }

            // Check Limit
            if ($data['hits'] > $limit) {
                $app->abort(429, 'Too Many Requests. Slow down!');
                return false;
            }

            // Update Cache
            $app->cache->put($key, $data, $minutes);
        }

        // Add Headers
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . max(0, $limit - $data['hits']));

        return true;
    }
}
