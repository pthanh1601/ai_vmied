<?php
namespace App\Middleware;

class AdminMiddleware
{
    public function handle($app)
    {
        // 1. Check if user is authenticated (using AuthMiddleware's logic implicitly via session)
        $sessionUser = $app->session->get('account');

        if (!$sessionUser) {
            header('Location: /login');
            exit;
        }

        // 2. Check if user is Admin (type == 1)
        $isAdmin = app()->db->has("accounts", [
            "uuid"    => $sessionUser['uuid'],
            "type"    => 1,
            "status"  => 1,
            "deleted" => 0
        ]);

        if (!$isAdmin) {
            header('Location: /app'); // Redirect to user dashboard if not admin
            exit;
        }
        
        $app->request->user = (object) $sessionUser;
        return true;
    }
}
