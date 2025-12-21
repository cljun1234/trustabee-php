<?php
$activePage = 'campaigns';
$activeSubPage = 'social';
$pageTitle = 'Social Widget';
require_once __DIR__ . '/../layouts/header.php';

// Check feature access
$plan = PlanManager::getUserPlan($_SESSION['user_id']);
$isAllowed = !empty($plan['features']['socials']);
$brandingAllowed = !empty($plan['features']['remove_branding']);

// Constants
$PLATFORMS = SocialController::PLATFORMS_CONFIG;
?>

<?php if (!$isAllowed): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center mt-6">
        <i class="fa-solid fa-lock text-5xl text-gray-300 mb-6 block"></i>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Feature Locked</h2>
        <p class="text-gray-600 mb-6">Your current plan does not include Social Widgets.</p>
        <a href="/billing" class="inline-block bg-primary hover:bg-primary-hover text-white font-medium py-2 px-6 rounded-lg transition-colors">Upgrade Plan</a>
    </div>
    <?php require_once __DIR__ . '/../layouts/footer.php'; exit; ?>
<?php endif; ?>

<div class="max-w-6xl mx-auto">

    <!-- Top Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Social Widget</h2>
            <p class="text-sm text-gray-500">Configure your social media links and widget appearance.</p>
        </div>

        <!-- Global Enable Toggle -->
        <div class="flex items-center bg-white border border-gray-200 rounded-lg px-4 py-2 shadow-sm">
            <span class="mr-3 text-sm font-medium text-gray-700">Enable Widget</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="globalToggle" class="sr-only peer" <?php echo ($social['active'] ?? 0) ? 'checked' : ''; ?> onchange="toggleWidget(this.checked, <?php echo $social['id'] ?? 0; ?>)">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
        </div>
    </div>

    <form action="/campaigns/social/save" method="POST" class="space-y-6">
        <input type="hidden" name="social_id" value="<?php echo $social['id'] ?? ''; ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Appearance Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Appearance</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Widget Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($social['title'] ?? 'Follow Us'); ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                            <input type="text" name="subtitle" value="<?php echo htmlspecialchars($social['subtitle'] ?? ''); ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                            <select name="position" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="bottom-right" <?php echo ($social['position'] ?? '') == 'bottom-right' ? 'selected' : ''; ?>>Bottom Right</option>
                                <option value="bottom-left" <?php echo ($social['position'] ?? '') == 'bottom-left' ? 'selected' : ''; ?>>Bottom Left</option>
                            </select>
                        </div>

                        <div class="flex items-center pt-2">
                            <div class="flex h-5 items-center">
                                <input id="remove_branding" name="remove_branding" type="checkbox" <?php if(!$brandingAllowed) echo 'disabled'; ?> <?php if(!empty($social['remove_branding'])) echo 'checked'; ?> class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary disabled:opacity-50">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="remove_branding" class="font-medium text-gray-700 <?php if(!$brandingAllowed) echo 'opacity-50'; ?>">Remove Branding</label>
                                <?php if(!$brandingAllowed): ?>
                                    <span class="text-xs text-red-500 block">Upgrade required</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Triggers Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Rules</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trigger</label>
                            <select name="trigger_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="delay" <?php echo ($social['trigger_type'] ?? '') == 'delay' ? 'selected' : ''; ?>>Time Delay</option>
                                <option value="exit_intent" <?php echo ($social['trigger_type'] ?? '') == 'exit_intent' ? 'selected' : ''; ?>>Exit Intent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delay (Seconds)</label>
                            <input type="number" name="trigger_delay" value="<?php echo (int)($social['trigger_delay'] ?? 0); ?>" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select name="frequency" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="every_load" <?php echo ($social['frequency'] ?? '') == 'every_load' ? 'selected' : ''; ?>>Every Page Load</option>
                                <option value="session" <?php echo ($social['frequency'] ?? '') == 'session' ? 'selected' : ''; ?>>Once per Session</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary-hover text-white font-bold py-3 px-4 rounded-lg shadow-md transition-colors sticky bottom-4">
                    Save Changes
                </button>
            </div>

            <!-- Right: Platforms List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Social Platforms</h3>
                        <p class="text-xs text-gray-500 mt-1">Enable and configure the platforms you want to display.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <?php foreach ($PLATFORMS as $key => $config):
                            // Check if this platform has an entry in $links_map
                            $linkData = $links_map[$key] ?? null;
                            $isActive = $linkData && $linkData['is_active'];
                            $urlValue = $linkData ? $linkData['url'] : '';
                            $labelValue = $linkData ? $linkData['label_text'] : ucfirst($key);
                        ?>
                        <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start sm:items-center gap-4">
                                <!-- Icon -->
                                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white" style="background-color: <?php echo $config['color']; ?>;">
                                    <i class="<?php echo $config['icon']; ?> text-lg"></i>
                                </div>

                                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Toggle & Label -->
                                    <div class="flex items-center justify-between sm:justify-start sm:gap-4">
                                        <span class="font-bold text-gray-700 w-24 capitalize"><?php echo $key; ?></span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="platform_<?php echo $key; ?>_active" class="sr-only peer" <?php echo $isActive ? 'checked' : ''; ?> onchange="toggleInputs('<?php echo $key; ?>', this.checked)">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>

                                    <!-- Inputs Container -->
                                    <div id="inputs_<?php echo $key; ?>" class="grid grid-cols-1 gap-2 <?php echo $isActive ? '' : 'opacity-50 pointer-events-none'; ?>">
                                        <input type="text" name="platform_<?php echo $key; ?>_url" value="<?php echo htmlspecialchars($urlValue); ?>" placeholder="Profile URL (e.g. https://...)" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border">
                                        <input type="text" name="platform_<?php echo $key; ?>_label" value="<?php echo htmlspecialchars($labelValue); ?>" placeholder="Label (e.g. Follow us)" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-xs p-2 border">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleWidget(enabled, id) {
        fetch('/campaigns/social/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ enabled: enabled, id: id })
        })
        .then(res => res.json())
        .then(data => {
            if(!data.success) alert('Error saving state');
        })
        .catch(err => alert('Network error'));
    }

    function toggleInputs(key, checked) {
        const container = document.getElementById('inputs_' + key);
        if (checked) {
            container.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            container.classList.add('opacity-50', 'pointer-events-none');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
