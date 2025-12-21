<?php
$activePage = 'campaigns';
$activeSubPage = 'newsletter';
$pageTitle = 'Newsletter Leads';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
             <h2 class="text-2xl font-bold text-gray-800">Leads: <?php echo htmlspecialchars($newsletter['title']); ?></h2>
             <p class="text-sm text-gray-500">View and export subscribers captured by this form.</p>
        </div>
        <div class="flex gap-3">
            <a href="/campaigns/newsletter" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2.5 px-4 rounded-lg shadow-sm flex items-center transition-colors text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
            <a href="/campaigns/newsletter/export/<?php echo $newsletter['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm flex items-center transition-colors text-sm">
                <i class="fa-solid fa-file-csv mr-2"></i> Export CSV
            </a>
        </div>
    </div>

     <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($leads)): ?>
             <div class="text-center py-12">
                <div class="text-gray-300 mb-4">
                    <i class="fa-solid fa-users-slash text-5xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No leads yet</h3>
                <p class="text-gray-500">Share your newsletter form to start collecting leads.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <?php if ($newsletter['allow_name']): ?>
                                <th scope="col" class="px-6 py-3">Name</th>
                            <?php endif; ?>
                            <?php if ($newsletter['allow_phone']): ?>
                                <th scope="col" class="px-6 py-3">Phone</th>
                            <?php endif; ?>
                            <th scope="col" class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($leads as $lead): ?>
                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($lead['email']); ?></td>
                            <?php if ($newsletter['allow_name']): ?>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($lead['name'] ?? '-'); ?></td>
                            <?php endif; ?>
                            <?php if ($newsletter['allow_phone']): ?>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($lead['phone'] ?? '-'); ?></td>
                            <?php endif; ?>
                            <td class="px-6 py-4 text-gray-400 text-xs">
                                <?php echo date('M j, Y g:i a', strtotime($lead['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
