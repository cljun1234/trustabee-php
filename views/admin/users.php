<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Current Plan</th>
                <th>Subscribed Since</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <?php if(($user['role'] ?? 'user') == 'owner'): ?>
                        <span style="color: green; font-weight: bold;">Owner</span>
                    <?php else: ?>
                        User
                    <?php endif; ?>
                </td>
                <td>
                    <form action="/admin/users/plan" method="POST" style="display: flex; gap: 5px;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <select name="plan_id" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                            <?php foreach($allPlans as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($user['plan_id'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm" style="background: #1a73e8;">Update</button>
                    </form>
                </td>
                <td><?php echo ($user['subscription_start'] ?? false) ? date('M d, Y', strtotime($user['subscription_start'])) : '-'; ?></td>
                <td>
                    <!-- Toggle Role -->
                    <form action="/admin/users/role" method="POST" style="display: inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <?php if(($user['role'] ?? 'user') == 'owner'): ?>
                            <input type="hidden" name="role" value="user">
                            <button type="submit" class="btn btn-sm" style="background: #dc3545;" onclick="return confirm('Demote this admin?')">Demote</button>
                        <?php else: ?>
                            <input type="hidden" name="role" value="owner">
                            <button type="submit" class="btn btn-sm" style="background: #28a745;">Make Admin</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
