<?php
$activePage = 'campaigns';
$activeSubPage = $campaignType;
$pageTitle = ucwords(str_replace('-', ' ', $campaignType));
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-50 text-primary mb-6">
            <i class="fa-solid fa-person-digging text-3xl"></i>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-3">Coming Soon</h2>

        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            The <strong><?php echo htmlspecialchars($pageTitle); ?></strong> feature is currently under development.
            We're working hard to bring this to you!
        </p>

        <a href="/" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
            Back to Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
