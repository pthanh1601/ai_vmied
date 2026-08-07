<?php $this->extend('layouts/master') ?>

<?php $this->section('content') ?>
<div class="min-h-[50vh] flex flex-col items-center justify-center text-center">
    <div class="text-9xl font-black text-slate-200">404</div>
    <div class="text-2xl font-bold text-slate-800 mt-4"><?= $message ?? 'Page Not Found' ?></div>
    <p class="text-slate-500 mt-2">The page you are looking for does not exist or has been moved.</p>
    <a href="/" class="mt-8 px-6 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
        <i class="fas fa-home mr-2"></i> Go Back Home
    </a>
</div>
<?php $this->endSection() ?>