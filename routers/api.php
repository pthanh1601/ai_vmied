<?php
    $app->router('/api/copyleaks/webhook/{status}', 'POST', ['App\Controllers\MockOriginalityController', 'copyleaksWebhook']);
    $app->router('/api/vnpay-ipn', 'GET', ['App\Controllers\PaymentController', 'VnpayIpn']);

