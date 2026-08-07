<?php
    $app->router('/api/copyleaks/webhook/{status}', 'POST', ['App\Controllers\MockOriginalityController', 'copyleaksWebhook']);

