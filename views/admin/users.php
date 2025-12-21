<?php
$activePage = 'admin';
$activeSubPage = 'users';
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
             <h3 class="text-lg font-bold text-gray-800">Users Directory</h3>
             <span class="text-xs text-gray-500 bg-white border border-gray-200 px-3 py-1 rounded-full">
                Total: <?php echo count($users); ?>
             </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">User</th>
                        <th scope="col" class="px-6 py-3">Role</th>
                        <th scope="col" class="px-6 py-3">Current Plan</th>
                        <th scope="col" class="px-6 py-3">Subscribed Since</th>
                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($users as $user): ?>
                    <tr class="bg-white hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">#<?php echo $user['id']; ?></td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if(($user['role'] ?? 'user') == 'owner'): ?>
                                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded uppercase">Owner</span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded uppercase">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                             <form action="/admin/users/plan" method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="plan_id" onchange="this.form.submit()" class="block w-full min-w-[120px] rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-xs py-1 px-2 border bg-white">
                                    <?php foreach($allPlans as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($user['plan_id'] == $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <?php echo ($user['subscription_start'] ?? false) ? date('M d, Y', strtotime($user['subscription_start'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <form action="/admin/users/role" method="POST" class="inline-block">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if(($user['role'] ?? 'user') == 'owner'): ?>
                                    <input type="hidden" name="role" value="user">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium border border-red-200 hover:border-red-400 bg-red-50 px-2 py-1 rounded transition-colors" onclick="return confirm('Demote this admin?')">Demote</button>
                                <?php else: ?>
                                    <input type="hidden" name="role" value="owner">
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium border border-green-200 hover:border-green-400 bg-green-50 px-2 py-1 rounded transition-colors">Make Admin</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
