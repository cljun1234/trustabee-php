<?php
$activePage = 'campaigns';
$activeSubPage = 'coupon';
$pageTitle = 'Coupons';
require_once __DIR__ . '/../layouts/header.php';

// Check feature access
$plan = PlanManager::getUserPlan($_SESSION['user_id']);
$isAllowed = !empty($plan['features']['coupons']);
$brandingAllowed = !empty($plan['features']['remove_branding']);
?>

<?php if (!$isAllowed): ?>
    <div style="text-align: center; padding: 50px; background: white; border-radius: 8px; margin-top: 20px;">
        <i class="fa-solid fa-lock" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
        <h2>Feature Locked</h2>
        <p>Your current plan does not include Coupons.</p>
        <div style="margin-top: 20px;">
            <a href="/billing" class="btn">Upgrade Plan</a>
        </div>
    </div>
    <?php require_once __DIR__ . '/../layouts/footer.php'; exit; ?>
<?php endif; ?>

<style>
/* Switch Toggle CSS */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider { background-color: var(--primary-color); }
input:focus + .slider { box-shadow: 0 0 1px var(--primary-color); }
input:checked + .slider:before { transform: translateX(24px); }

.coupon-card {
    border: 1px solid #eee;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.coupon-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; }
.coupon-meta { color: #666; font-size: 0.9rem; }
.coupon-stats { display: flex; gap: 20px; margin-right: 20px; text-align: center; }
.stat-item { display: flex; flex-direction: column; }
.stat-value { font-weight: bold; font-size: 1.2rem; color: var(--primary-color); }
.stat-name { font-size: 0.8rem; color: #777; }

/* Modal Styles for Edit/Create */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; }
.close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }

.form-row { display: flex; gap: 15px; margin-bottom: 15px; }
.form-col { flex: 1; }
</style>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Manage Coupons</h2>
        <button class="btn" onclick="openModal()">+ New Coupon</button>
    </div>

    <?php if (empty($coupons)): ?>
        <p style="text-align: center; color: #777; padding: 20px;">No coupons created yet.</p>
    <?php else: ?>
        <?php foreach ($coupons as $c): ?>
            <div class="coupon-card" style="opacity: <?php echo $c['active'] ? '1' : '0.6'; ?>">
                <div class="coupon-info">
                    <h3><?php echo htmlspecialchars($c['title']); ?> <span style="font-size: 0.8rem; background: #eee; padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($c['coupon_code']); ?></span></h3>
                    <div class="coupon-meta">
                        <?php echo htmlspecialchars($c['trigger_type'] == 'exit_intent' ? 'Exit Intent' : 'Delay: ' . $c['trigger_delay'] . 's'); ?> &bull;
                        <?php echo htmlspecialchars($c['frequency'] == 'session' ? 'Once per session' : 'Every page load'); ?>
                        <?php if($c['match_url']): ?> &bull; URL: <?php echo htmlspecialchars($c['match_url']); ?><?php endif; ?>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div class="coupon-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $c['views']; ?></span>
                            <span class="stat-name">Views</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $c['clicks']; ?></span>
                            <span class="stat-name">Clicks</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn" style="background: #6c757d;" onclick='editCoupon(<?php echo json_encode($c); ?>)'>Edit</button>
                        <a href="/campaigns/coupon/delete/<?php echo $c['id']; ?>" class="btn btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="couponModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Create Coupon</h2>

        <form action="/campaigns/coupon/save" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="coupon_id" id="coupon_id">

            <div class="form-row">
                <div class="form-col">
                    <label>Title</label>
                    <input type="text" name="title" id="title" required value="Special Offer">
                </div>
                <div class="form-col">
                    <label>Coupon Code</label>
                    <input type="text" name="coupon_code" id="coupon_code" required value="SALE20">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" id="description" placeholder="e.g. Get 20% off your next purchase">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Button Text</label>
                    <input type="text" name="button_text" id="button_text" value="Copy Code">
                </div>
                <div class="form-col">
                    <label>Colors (Bg / Text)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="color" name="bg_color" id="bg_color" value="#ffffff" style="height: 38px; padding: 2px;">
                        <input type="color" name="text_color" id="text_color" value="#333333" style="height: 38px; padding: 2px;">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Banner Image (Max 15MB)</label>
                    <input type="file" name="image_upload" id="image_upload" accept="image/*">
                    <div id="current_image_display" style="margin-top: 5px; font-size: 0.8rem; color: #666; display: none;">
                        Current: <a href="#" target="_blank" id="current_image_link">View</a>
                    </div>
                </div>
                <div class="form-col">
                    <label>Banner Position</label>
                    <select name="image_style" id="image_style" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="top">Top</option>
                        <option value="left">Left (Split)</option>
                        <option value="right">Right (Split)</option>
                        <option value="background">Background</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="toggle" <?php if(!$brandingAllowed) echo 'style="opacity: 0.5;" title="Upgrade required"'; ?>>
                    <input type="checkbox" name="remove_branding" id="remove_branding" <?php if(!$brandingAllowed) echo 'disabled'; ?>>
                    Remove Branding <?php if(!$brandingAllowed) echo ' <small>(Upgrade Required)</small>'; ?>
                </label>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
            <h3>Rules</h3>

            <div class="form-row">
                <div class="form-col">
                    <label>Trigger</label>
                    <select name="trigger_type" id="trigger_type" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="delay">Time Delay</option>
                        <option value="exit_intent">Exit Intent</option>
                    </select>
                </div>
                <div class="form-col">
                    <label>Delay (Seconds)</label>
                    <input type="number" name="trigger_delay" id="trigger_delay" value="0" min="0" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Frequency</label>
                    <select name="frequency" id="frequency" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="every_load">Every Page Load</option>
                        <option value="session">Once per Visitor (30 mins)</option>
                    </select>
                </div>
                <div class="form-col">
                    <label>Match URL (Optional)</label>
                    <input type="text" name="match_url" id="match_url" placeholder="e.g. /pricing">
                </div>
            </div>

             <div class="form-group">
                <label class="toggle">
                    <input type="checkbox" name="active" id="active" checked>
                    Enable this coupon
                </label>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" class="btn">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("couponModal");
    const modalTitle = document.getElementById("modalTitle");
    const formInputs = {
        coupon_id: document.getElementById("coupon_id"),
        title: document.getElementById("title"),
        coupon_code: document.getElementById("coupon_code"),
        description: document.getElementById("description"),
        button_text: document.getElementById("button_text"),
        bg_color: document.getElementById("bg_color"),
        text_color: document.getElementById("text_color"),
        trigger_type: document.getElementById("trigger_type"),
        trigger_delay: document.getElementById("trigger_delay"),
        frequency: document.getElementById("frequency"),
        match_url: document.getElementById("match_url"),
        active: document.getElementById("active"),
        image_style: document.getElementById("image_style"),
        remove_branding: document.getElementById("remove_branding")
    };

    const currentImageDisplay = document.getElementById("current_image_display");
    const currentImageLink = document.getElementById("current_image_link");

    function openModal() {
        modal.style.display = "block";
        resetForm();
    }

    function closeModal() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    function resetForm() {
        modalTitle.textContent = "Create Coupon";
        formInputs.coupon_id.value = "";
        formInputs.title.value = "Special Offer";
        formInputs.coupon_code.value = "";
        formInputs.description.value = "";
        formInputs.button_text.value = "Copy Code";
        formInputs.bg_color.value = "#ffffff";
        formInputs.text_color.value = "#333333";
        formInputs.trigger_type.value = "delay";
        formInputs.trigger_delay.value = "0";
        formInputs.frequency.value = "every_load";
        formInputs.match_url.value = "";
        formInputs.active.checked = true;
        formInputs.image_style.value = "top";
        formInputs.remove_branding.checked = false;
        currentImageDisplay.style.display = "none";
        document.getElementById("image_upload").value = "";
    }

    function editCoupon(data) {
        modalTitle.textContent = "Edit Coupon";
        formInputs.coupon_id.value = data.id;
        formInputs.title.value = data.title;
        formInputs.coupon_code.value = data.coupon_code;
        formInputs.description.value = data.description || "";
        formInputs.button_text.value = data.button_text;
        formInputs.bg_color.value = data.bg_color;
        formInputs.text_color.value = data.text_color;
        formInputs.trigger_type.value = data.trigger_type;
        formInputs.trigger_delay.value = data.trigger_delay;
        formInputs.frequency.value = data.frequency;
        formInputs.match_url.value = data.match_url || "";
        formInputs.active.checked = data.active == 1;
        formInputs.image_style.value = data.image_style || "top";
        formInputs.remove_branding.checked = data.remove_branding == 1;

        if (data.image_url) {
            currentImageDisplay.style.display = "block";
            currentImageLink.href = data.image_url;
        } else {
            currentImageDisplay.style.display = "none";
        }
        document.getElementById("image_upload").value = "";

        modal.style.display = "block";
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
