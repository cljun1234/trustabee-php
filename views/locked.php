<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="bg-white p-10 rounded-2xl shadow-xl border border-gray-100 max-w-lg w-full transform transition-all hover:scale-[1.01]">
        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-lock text-3xl text-gray-400"></i>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-3">Feature Locked</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            This feature is not available in your current plan (<strong><?php echo htmlspecialchars($plan['name'] ?? 'Current'); ?></strong>).
            Upgrade your subscription to unlock it.
        </p>

        <a href="/billing" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-sm transition-all transform hover:-translate-y-0.5">
            <i class="fa-solid fa-rocket mr-2"></i> Upgrade Plan
        </a>

        <p class="mt-6 text-sm">
            <a href="/billing" class="text-gray-400 hover:text-gray-600 transition-colors">View Plans</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
