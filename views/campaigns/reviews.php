<?php
$pageTitle = "Reviews";
$activePage = "campaigns";
$activeSubPage = "reviews";
require_once __DIR__ . '/../layouts/header.php';
?>

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
.form-group.toggle { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.form-group.toggle label { margin-bottom: 0; font-size: 1.1rem; font-weight: 600; }

/* Preview Container */
.preview-box {
    background: #f4f6f8;
    border: 1px dashed #ccc;
    border-radius: 8px;
    padding: 30px;
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Mock Popup */
.mock-popup {
    background: white;
    border-radius: 12px;
    padding: 30px;
    width: 320px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    text-align: center;
    position: relative;
}
.mock-popup h3 { font-size: 1.2rem; margin: 0 0 10px 0; color: #333; }
.mock-popup p { font-size: 0.9rem; color: #666; margin: 0 0 20px 0; }
.mock-stars { font-size: 24px; color: #ccc; margin-bottom: 20px; }
.mock-stars span { margin: 0 2px; }
.mock-branding { font-size: 10px; color: #999; margin-top: 15px; }

/* Mock Toaster */
.mock-toaster {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 10px;
    display: flex;
    align-items: center;
    width: 300px;
    border-left: 4px solid var(--primary-color);
}
.mock-toaster-img {
    width: 40px; height: 40px; background: #eee; border-radius: 50%;
    margin-right: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    color: #888; font-weight: bold;
}
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        <!-- GLOBAL ENABLE -->
        <div class="card mb-4">
            <div class="form-group toggle" style="margin-bottom: 0; padding: 10px 0;">
                <div style="display: flex; flex-direction: column;">
                    <label style="margin-bottom: 5px;">Enable Reviews Feature</label>
                    <span style="font-size: 0.9rem; color: #666; font-weight: normal;">Turn on to start collecting and displaying reviews.</span>
                </div>
                <label class="switch">
                    <input type="checkbox" id="reviewsActive"
                        <?php echo ($review['active'] ?? 0) ? 'checked' : ''; ?>
                        onchange="toggleFeature(<?php echo $review['id']; ?>, this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <form id="reviewsConfigForm">
            <input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>">
            <input type="hidden" name="active" value="<?php echo $review['active']; ?>">
            <!-- Hidden fields updated by JS/Toggles -->
            <input type="hidden" name="show_reviews_widget" id="input_show_widget" value="<?php echo $review['show_reviews_widget']; ?>">
            <input type="hidden" name="remove_branding" id="input_remove_branding" value="<?php echo $review['remove_branding']; ?>">
            <input type="hidden" name="widget_position" id="input_widget_position" value="<?php echo $review['widget_position']; ?>">

            <!-- COLLECTION POPUP -->
            <div class="card mb-4">
                <div class="card-header">Review Collection Popup</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3 text-muted">Popup Settings</h5>

                            <div class="mb-3">
                                <label class="form-label">Popup Title</label>
                                <input type="text" class="form-control" name="popup_title" id="input_popup_title"
                                       value="<?php echo htmlspecialchars($review['popup_title'] ?? 'Rate your experience'); ?>"
                                       oninput="updatePreview()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="popup_description" id="input_popup_desc"
                                       value="<?php echo htmlspecialchars($review['popup_description'] ?? ''); ?>"
                                       oninput="updatePreview()">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Google Review Link</label>
                                    <input type="url" class="form-control" name="google_review_link" placeholder="https://g.page/..." value="<?php echo htmlspecialchars($review['google_review_link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Facebook Review Link</label>
                                    <input type="url" class="form-control" name="facebook_review_link" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($review['facebook_review_link'] ?? ''); ?>">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3 text-primary"><i class="fa fa-star"></i> High Rating Logic (4-5 Stars)</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Post-Click Action</label>
                                    <select class="form-select" name="high_star_action" onchange="updateActionFields('high', this.value)">
                                        <option value="thank_you" <?php echo ($review['high_star_action'] == 'thank_you') ? 'selected' : ''; ?>>Show Message</option>
                                        <option value="coupon" <?php echo ($review['high_star_action'] == 'coupon') ? 'selected' : ''; ?>>Show Coupon</option>
                                        <option value="redirect" <?php echo ($review['high_star_action'] == 'redirect') ? 'selected' : ''; ?>>Redirect URL</option>
                                        <option value="close" <?php echo ($review['high_star_action'] == 'close') ? 'selected' : ''; ?>>Close Popup</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Dynamic High Action Fields -->
                            <div class="action-field-high" id="high_thank_you" style="display:none;">
                                <label class="form-label">Thank You Message</label>
                                <input type="text" class="form-control" name="high_star_message" value="<?php echo htmlspecialchars($review['high_star_message'] ?? 'Thank you for your review!'); ?>">
                            </div>
                            <div class="action-field-high" id="high_coupon" style="display:none;">
                                <label class="form-label">Select Coupon</label>
                                <select class="form-select" name="high_star_coupon_id">
                                    <option value="">-- Select Coupon --</option>
                                    <?php foreach ($coupons as $cp): ?>
                                        <option value="<?php echo $cp['id']; ?>" <?php echo ($review['high_star_coupon_id'] == $cp['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cp['title'] . ' (' . $cp['coupon_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="action-field-high" id="high_redirect" style="display:none;">
                                <label class="form-label">Redirect URL</label>
                                <input type="url" class="form-control" name="high_star_redirect_url" value="<?php echo htmlspecialchars($review['high_star_redirect_url'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3 text-warning"><i class="fa fa-star-half-stroke"></i> Low Rating Logic (1-3 Stars)</h6>
                             <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Post-Submit Action</label>
                                    <select class="form-select" name="low_star_action" onchange="updateActionFields('low', this.value)">
                                        <option value="thank_you" <?php echo ($review['low_star_action'] == 'thank_you') ? 'selected' : ''; ?>>Show Message</option>
                                        <option value="coupon" <?php echo ($review['low_star_action'] == 'coupon') ? 'selected' : ''; ?>>Show Coupon</option>
                                        <option value="redirect" <?php echo ($review['low_star_action'] == 'redirect') ? 'selected' : ''; ?>>Redirect URL</option>
                                        <option value="close" <?php echo ($review['low_star_action'] == 'close') ? 'selected' : ''; ?>>Close Popup</option>
                                    </select>
                                </div>
                            </div>
                             <!-- Dynamic Low Action Fields -->
                            <div class="action-field-low" id="low_thank_you" style="display:none;">
                                <label class="form-label">Thank You Message</label>
                                <input type="text" class="form-control" name="low_star_message" value="<?php echo htmlspecialchars($review['low_star_message'] ?? 'Thank you for your feedback.'); ?>">
                            </div>
                            <div class="action-field-low" id="low_coupon" style="display:none;">
                                <label class="form-label">Select Coupon</label>
                                <select class="form-select" name="low_star_coupon_id">
                                    <option value="">-- Select Coupon --</option>
                                    <?php foreach ($coupons as $cp): ?>
                                        <option value="<?php echo $cp['id']; ?>" <?php echo ($review['low_star_coupon_id'] == $cp['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cp['title'] . ' (' . $cp['coupon_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="action-field-low" id="low_redirect" style="display:none;">
                                <label class="form-label">Redirect URL</label>
                                <input type="url" class="form-control" name="low_star_redirect_url" value="<?php echo htmlspecialchars($review['low_star_redirect_url'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">
                             <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Trigger</label>
                                    <select class="form-select" name="trigger_type">
                                        <option value="delay" <?php echo ($review['trigger_type'] == 'delay') ? 'selected' : ''; ?>>Delay</option>
                                        <option value="exit_intent" <?php echo ($review['trigger_type'] == 'exit_intent') ? 'selected' : ''; ?>>Exit Intent</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Delay (sec)</label>
                                    <input type="number" class="form-control" name="trigger_delay" value="<?php echo $review['trigger_delay']; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Frequency</label>
                                    <select class="form-select" name="frequency">
                                        <option value="every_load" <?php echo ($review['frequency'] == 'every_load') ? 'selected' : ''; ?>>Every Load</option>
                                        <option value="session" <?php echo ($review['frequency'] == 'session') ? 'selected' : ''; ?>>Once/Session</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Preview</label>
                            <div class="preview-box">
                                <div class="mock-popup">
                                    <div style="position:absolute;top:10px;right:15px;color:#ccc;">&times;</div>
                                    <h3 id="preview_title">Rate your experience</h3>
                                    <p id="preview_desc"></p>
                                    <div class="mock-stars">
                                        &#9733; &#9733; &#9733; &#9733; &#9733;
                                    </div>
                                    <div class="mock-branding" id="preview_branding_popup">Powered by Trustabee</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DISPLAY WIDGET -->
            <div class="card mb-4">
                <div class="card-header">Review Display Widget</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group toggle mt-2">
                                <label>Show Reviews Toaster</label>
                                <label class="switch">
                                    <input type="checkbox" id="showWidgetToggle"
                                        <?php echo ($review['show_reviews_widget'] ?? 0) ? 'checked' : ''; ?>
                                        onchange="updateHiddenInput('input_show_widget', this.checked)">
                                    <span class="slider"></span>
                                </label>
                             </div>

                             <div class="mb-3">
                                 <label class="form-label">Position</label>
                                 <select class="form-select" onchange="updateHiddenInput('input_widget_position', this.value)">
                                     <option value="bottom-left" <?php echo ($review['widget_position'] == 'bottom-left') ? 'selected' : ''; ?>>Bottom Left</option>
                                     <option value="bottom-right" <?php echo ($review['widget_position'] == 'bottom-right') ? 'selected' : ''; ?>>Bottom Right</option>
                                     <option value="top-left" <?php echo ($review['widget_position'] == 'top-left') ? 'selected' : ''; ?>>Top Left</option>
                                     <option value="top-right" <?php echo ($review['widget_position'] == 'top-right') ? 'selected' : ''; ?>>Top Right</option>
                                 </select>
                             </div>

                             <div class="form-group toggle">
                                <label>Remove Branding</label>
                                <label class="switch">
                                    <input type="checkbox" id="brandingToggle"
                                        <?php echo ($review['remove_branding'] ?? 0) ? 'checked' : ''; ?>
                                        onchange="updateHiddenInput('input_remove_branding', this.checked); updatePreview();">
                                    <span class="slider"></span>
                                </label>
                             </div>
                        </div>
                        <div class="col-md-6">
                             <label class="form-label text-muted">Preview</label>
                             <div class="preview-box">
                                 <div class="mock-toaster">
                                     <div class="mock-toaster-img">J</div>
                                     <div style="flex-grow:1; text-align:left;">
                                         <div style="font-weight:bold; font-size:0.9rem;">John Doe <span style="color:#fbbf24; font-size:0.8rem;">&#9733;&#9733;&#9733;&#9733;&#9733;</span></div>
                                         <div style="font-size:0.8rem; color:#555;">"Great service!" <a href="#" style="color:var(--primary-color);">View on Google</a></div>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                 <button type="button" class="btn btn-primary btn-lg" onclick="saveConfig()">Save All Changes</button>
            </div>
        </form>

        <!-- MANUAL REVIEWS -->
        <div class="card mb-4">
             <div class="card-header d-flex justify-content-between align-items-center">
                 <span>Manual Reviews List</span>
                 <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addReviewModal"><i class="fa fa-plus"></i> Add New</button>
             </div>
             <div class="card-body p-0">
                 <div class="table-responsive">
                     <table class="table table-hover mb-0">
                         <thead class="bg-light">
                             <tr>
                                 <th class="border-0">Reviewer</th>
                                 <th class="border-0">Rating</th>
                                 <th class="border-0">Source</th>
                                 <th class="border-0">Snippet</th>
                                 <th class="border-0 text-end">Action</th>
                             </tr>
                         </thead>
                         <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="align-middle">
                                    <?php if($item['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width:30px;height:30px;border-radius:50%;margin-right:10px;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:30px;height:30px;border-radius:50%;background:#eee;display:inline-flex;align-items:center;justify-content:center;margin-right:10px;font-weight:bold;color:#666;">
                                            <?php echo strtoupper(substr($item['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </td>
                                <td class="align-middle"><?php echo $item['rating']; ?> <i class="fa fa-star text-warning" style="font-size:0.8rem;"></i></td>
                                <td class="align-middle">
                                    <?php
                                        $icon = 'fa-user';
                                        if($item['source'] == 'google') $icon = 'fa-google';
                                        if($item['source'] == 'facebook') $icon = 'fa-facebook';
                                    ?>
                                    <i class="fab <?php echo $icon; ?> text-muted me-1"></i> <?php echo ucfirst($item['source']); ?>
                                </td>
                                <td class="align-middle text-muted"><small><?php echo substr(htmlspecialchars($item['review_text']), 0, 40) . (strlen($item['review_text'])>40 ? '...' : ''); ?></small></td>
                                <td class="align-middle text-end">
                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteItem(<?php echo $item['id']; ?>)"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($items)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No manual reviews yet. Add one to display in the toaster widget.</td></tr>
                            <?php endif; ?>
                         </tbody>
                     </table>
                 </div>
             </div>
        </div>

        <!-- FEEDBACKS -->
        <div class="card mb-4">
            <div class="card-header">Collected Feedbacks (1-3 Stars)</div>
            <div class="card-body p-0">
                 <div class="table-responsive">
                     <table class="table table-striped mb-0">
                        <thead class="bg-light">
                             <tr>
                                 <th class="border-0">Date</th>
                                 <th class="border-0">Rating</th>
                                 <th class="border-0">Visitor</th>
                                 <th class="border-0">Feedback</th>
                             </tr>
                         </thead>
                         <tbody>
                            <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td class="align-middle"><small class="text-muted"><?php echo date('M d, Y', strtotime($fb['created_at'])); ?></small></td>
                                <td class="align-middle"><?php echo $fb['rating']; ?> <i class="fa fa-star text-warning" style="font-size:0.8rem;"></i></td>
                                <td class="align-middle">
                                    <div><?php echo htmlspecialchars($fb['name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($fb['email']); ?></small>
                                </td>
                                <td class="align-middle"><?php echo nl2br(htmlspecialchars($fb['feedback'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                             <?php if(empty($feedbacks)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No feedbacks collected yet.</td></tr>
                            <?php endif; ?>
                         </tbody>
                     </table>
                 </div>
            </div>
        </div>

    </div>
</div>

<!-- ADD REVIEW MODAL -->
<div class="modal fade" id="addReviewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Manual Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addReviewForm">
            <input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>">
            <div class="mb-3">
                <label class="form-label">Reviewer Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Review Text</label>
                <textarea class="form-control" name="review_text" rows="3"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rating</label>
                    <select class="form-select" name="rating">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                     <label class="form-label">Source</label>
                    <select class="form-select" name="source">
                        <option value="custom">Custom</option>
                        <option value="google">Google</option>
                        <option value="facebook">Facebook</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Photo URL (Optional)</label>
                <input type="url" class="form-control" name="image_url" placeholder="https://...">
            </div>
            <div class="mb-3">
                <label class="form-label">Source Link (View on...)</label>
                <input type="url" class="form-control" name="source_link" placeholder="https://...">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="saveItem()">Save Review</button>
      </div>
    </div>
  </div>
</div>

<script>
function updateActionFields(prefix, action) {
    document.querySelectorAll('.action-field-' + prefix).forEach(el => el.style.display = 'none');
    if (action === 'thank_you') document.getElementById(prefix + '_thank_you').style.display = 'block';
    if (action === 'coupon') document.getElementById(prefix + '_coupon').style.display = 'block';
    if (action === 'redirect') document.getElementById(prefix + '_redirect').style.display = 'block';
}

// Init fields
updateActionFields('high', '<?php echo $review['high_star_action'] ?? 'thank_you'; ?>');
updateActionFields('low', '<?php echo $review['low_star_action'] ?? 'thank_you'; ?>');

function updateHiddenInput(id, value) {
    // If it's a checkbox bool, convert to 1/0
    if (typeof value === 'boolean') value = value ? 1 : 0;
    document.getElementById(id).value = value;
}

function updatePreview() {
    // Title & Desc
    document.getElementById('preview_title').textContent = document.getElementById('input_popup_title').value || 'Rate your experience';
    const desc = document.getElementById('input_popup_desc').value;
    document.getElementById('preview_desc').textContent = desc;
    document.getElementById('preview_desc').style.display = desc ? 'block' : 'none';

    // Branding
    const removeBranding = document.getElementById('brandingToggle').checked;
    document.getElementById('preview_branding_popup').style.display = removeBranding ? 'none' : 'block';
}

// Init Preview
updatePreview();

function toggleFeature(reviewId, enabled) {
    // Reuse saveConfig to save 'active' state
    const form = document.getElementById('reviewsConfigForm');
    form.querySelector('input[name="active"]').value = enabled ? 1 : 0;
    saveConfig(true);
}

function saveConfig(silent = false) {
    const form = document.getElementById('reviewsConfigForm');
    const data = Object.fromEntries(new FormData(form).entries());

    fetch('/campaigns/review/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            if(!silent) alert('Settings Saved');
            else console.log('Toggled active state');
        } else {
            alert('Error: ' + res.error);
        }
    });
}

function saveItem() {
    const form = document.getElementById('addReviewForm');
    const data = Object.fromEntries(new FormData(form).entries());

    fetch('/campaigns/review/item/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            location.reload();
        } else {
            alert('Error: ' + res.error);
        }
    });
}

function deleteItem(id) {
    if(!confirm('Delete this review?')) return;
    fetch('/campaigns/review/item/delete/' + id)
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            location.reload();
        } else {
            alert('Error: ' + res.error);
        }
    });
}
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
