<?php
$activePage = 'campaigns';
$activeSubPage = 'video';
$pageTitle = 'Videos';
require_once __DIR__ . '/../layouts/header.php';

// Check feature access
$plan = PlanManager::getUserPlan($_SESSION['user_id']);
$isAllowed = !empty($plan['features']['videos']);
$brandingAllowed = !empty($plan['features']['remove_branding']);
?>

<?php if (!$isAllowed): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center mt-6">
        <i class="fa-solid fa-lock text-5xl text-gray-300 mb-6 block"></i>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Feature Locked</h2>
        <p class="text-gray-600 mb-6">Your current plan does not include Video Popups.</p>
        <a href="/billing" class="inline-block bg-primary hover:bg-primary-hover text-white font-medium py-2 px-6 rounded-lg transition-colors">Upgrade Plan</a>
    </div>
    <?php require_once __DIR__ . '/../layouts/footer.php'; exit; ?>
<?php endif; ?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manage Videos</h2>
            <p class="text-sm text-gray-500">Engage visitors with video popups from YouTube, Vimeo, or hosted files.</p>
        </div>
        <button onclick="openModal()" class="bg-primary hover:bg-primary-hover text-white font-medium py-2.5 px-6 rounded-lg shadow-sm flex items-center transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> New Video
        </button>
    </div>

    <?php if (empty($videos)): ?>
         <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fa-solid fa-video text-5xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No videos yet</h3>
            <p class="text-gray-500 mb-6">Add a video popup to increase engagement.</p>
            <button onclick="openModal()" class="text-primary hover:text-primary-hover font-medium">
                Create Video &rarr;
            </button>
        </div>
    <?php else: ?>
         <div class="grid grid-cols-1 gap-6">
            <?php foreach ($videos as $v): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md <?php echo $v['active'] ? '' : 'opacity-70 grayscale'; ?>">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

                        <!-- Info -->
                        <div class="flex-grow">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($v['title']); ?></h3>
                                <?php if(!$v['active']): ?>
                                    <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded">Inactive</span>
                                <?php endif; ?>
                            </div>
                             <div class="text-sm text-gray-500 flex flex-wrap gap-x-4 gap-y-1">
                                <span class="flex items-center"><i class="fa-solid fa-bolt w-4 text-center mr-1"></i>
                                    <?php echo htmlspecialchars($v['trigger_type'] == 'exit_intent' ? 'Exit Intent' : 'Delay: ' . $v['trigger_delay'] . 's'); ?>
                                </span>
                                <span class="flex items-center"><i class="fa-solid fa-arrows-rotate w-4 text-center mr-1"></i>
                                    <?php echo htmlspecialchars($v['frequency'] == 'session' ? 'Once/Session' : 'Every Load'); ?>
                                </span>
                                <?php if($v['match_url']): ?>
                                    <span class="flex items-center"><i class="fa-solid fa-link w-4 text-center mr-1"></i> <?php echo htmlspecialchars($v['match_url']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                         <!-- Stats & Actions -->
                        <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                            <div class="flex gap-6 text-center">
                                <div>
                                    <div class="text-xl font-bold text-primary"><?php echo number_format($v['views']); ?></div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">Views</div>
                                </div>
                                <div>
                                    <div class="text-xl font-bold text-green-600"><?php echo number_format($v['clicks']); ?></div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">Clicks</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button onclick='editVideo(<?php echo json_encode($v); ?>)' class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="/campaigns/video/delete/<?php echo $v['id']; ?>" onclick="return confirm('Are you sure?')" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="videoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

            <!-- Header -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Create Video</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form action="/campaigns/video/save" method="POST">
                <div class="px-4 py-5 sm:p-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <input type="hidden" name="video_id" id="video_id">

                    <!-- Content -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" id="title" required value="Check this out" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
                             <textarea name="message" id="message" rows="3" placeholder="Enter description..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border"></textarea>
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Video URL (YouTube, Vimeo, or Hosted)</label>
                             <input type="text" name="video_url" id="video_url" required placeholder="https://www.youtube.com/watch?v=..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="sm:col-span-2 text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Call to Action</div>

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                            <input type="text" name="btn_text" id="btn_text" value="Learn More" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button Action</label>
                             <select name="btn_action" id="btn_action" onchange="toggleLinkInput()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="link">Go to URL</option>
                                <option value="close">Close Popup</option>
                            </select>
                        </div>

                         <div id="link_group" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destination URL</label>
                            <input type="text" name="btn_link" id="btn_link" placeholder="https://example.com/page" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                    </div>

                    <!-- Styling -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Colors (Bg / Text)</label>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <input type="color" name="bg_color" id="bg_color" value="#ffffff" class="w-full h-10 p-0 border-0 rounded cursor-pointer">
                                </div>
                                <div class="flex-1">
                                    <input type="color" name="text_color" id="text_color" value="#333333" class="w-full h-10 p-0 border-0 rounded cursor-pointer">
                                </div>
                            </div>
                        </div>
                         <div class="flex items-center pt-6">
                             <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="remove_branding" name="remove_branding" type="checkbox" <?php if(!$brandingAllowed) echo 'disabled'; ?> class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary disabled:opacity-50">
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

                    <!-- Rules Header -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white px-2 text-sm text-gray-500 font-medium">Rules & Triggers</span>
                        </div>
                    </div>

                    <!-- Rules -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trigger</label>
                            <select name="trigger_type" id="trigger_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="delay">Time Delay</option>
                                <option value="exit_intent">Exit Intent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delay (Seconds)</label>
                            <input type="number" name="trigger_delay" id="trigger_delay" value="0" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select name="frequency" id="frequency" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border bg-white">
                                <option value="every_load">Every Page Load</option>
                                <option value="session">Once per Visitor (30m)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Match URL (Optional)</label>
                            <input type="text" name="match_url" id="match_url" placeholder="e.g. /pricing" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2.5 border">
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="bg-gray-50 p-4 rounded-lg flex items-center justify-between border border-gray-100">
                        <span class="flex-grow flex flex-col">
                            <span class="text-sm font-medium text-gray-900">Enable Video</span>
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="active" id="active" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                </div>

                 <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Save Video
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById("videoModal");
    const modalTitle = document.getElementById("modalTitle");
    const linkGroup = document.getElementById("link_group");
    const formInputs = {
        video_id: document.getElementById("video_id"),
        title: document.getElementById("title"),
        message: document.getElementById("message"),
        video_url: document.getElementById("video_url"),
        btn_text: document.getElementById("btn_text"),
        btn_action: document.getElementById("btn_action"),
        btn_link: document.getElementById("btn_link"),
        bg_color: document.getElementById("bg_color"),
        text_color: document.getElementById("text_color"),
        trigger_type: document.getElementById("trigger_type"),
        trigger_delay: document.getElementById("trigger_delay"),
        frequency: document.getElementById("frequency"),
        match_url: document.getElementById("match_url"),
        active: document.getElementById("active"),
        remove_branding: document.getElementById("remove_branding")
    };

    function openModal() {
        modal.classList.remove('hidden');
        resetForm();
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function toggleLinkInput() {
        if (formInputs.btn_action.value === 'link') {
            linkGroup.style.display = 'block';
        } else {
            linkGroup.style.display = 'none';
        }
    }

    function resetForm() {
        modalTitle.textContent = "Create Video";
        formInputs.video_id.value = "";
        formInputs.title.value = "Check this out";
        formInputs.message.value = "";
        formInputs.video_url.value = "";
        formInputs.btn_text.value = "Learn More";
        formInputs.btn_action.value = "link";
        formInputs.btn_link.value = "";
        formInputs.bg_color.value = "#ffffff";
        formInputs.text_color.value = "#333333";
        formInputs.trigger_type.value = "delay";
        formInputs.trigger_delay.value = "0";
        formInputs.frequency.value = "every_load";
        formInputs.match_url.value = "";
        formInputs.active.checked = true;
        formInputs.remove_branding.checked = false;
        toggleLinkInput();
    }

    function editVideo(data) {
        modalTitle.textContent = "Edit Video";
        formInputs.video_id.value = data.id;
        formInputs.title.value = data.title;
        formInputs.message.value = data.message || "";
        formInputs.video_url.value = data.video_url;
        formInputs.btn_text.value = data.btn_text;
        formInputs.btn_action.value = data.btn_action;
        formInputs.btn_link.value = data.btn_link || "";
        formInputs.bg_color.value = data.bg_color;
        formInputs.text_color.value = data.text_color;
        formInputs.trigger_type.value = data.trigger_type;
        formInputs.trigger_delay.value = data.trigger_delay;
        formInputs.frequency.value = data.frequency;
        formInputs.match_url.value = data.match_url || "";
        formInputs.active.checked = data.active == 1;
        formInputs.remove_branding.checked = data.remove_branding == 1;
        toggleLinkInput();

        modal.classList.remove('hidden');
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
