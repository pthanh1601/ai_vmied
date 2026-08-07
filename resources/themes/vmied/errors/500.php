<?php $this->extend('layouts/master') ?>

<?php $this->section('content') ?>
<div class="min-h-[50vh] flex flex-col items-center justify-center text-center">
    <div class="text-9xl font-black text-red-100">500</div>
    <div class="text-xl font-bold text-red-700 mt-4"><?= $message ?? 'Internal Server Error' ?></div>
    <p class="text-red-500 mt-2 max-w-lg mx-auto">Something went wrong on our servers. We are working to fix this.</p>

    <?php if (isset($code)): ?>
        <code class="mt-4 block bg-red-50 text-red-800 p-2 rounded text-xs opacity-75">Error Code: <?= $code ?></code>
    <?php endif; ?>

    <a href="/" class="mt-8 px-6 py-3 rounded-lg bg-slate-800 text-white font-medium hover:bg-slate-700 transition">
        <i class="fas fa-home mr-2"></i> Return Home
    </a>
</div>
<?php $this->endSection() ?>