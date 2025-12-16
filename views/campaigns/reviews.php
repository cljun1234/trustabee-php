<?php
$pageTitle = "Reviews";
$activePage = "campaigns";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Reviews Feature</h4>
                            <p class="text-muted mb-0">Collect reviews from visitors and display your best reviews to build trust.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="reviewsActive"
                                <?php echo ($review['active'] ?? 0) ? 'checked' : ''; ?>
                                onchange="toggleFeature(<?php echo $review['id']; ?>, this.checked)">
                            <label class="form-check-label" for="reviewsActive">Enable Feature</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-collection-tab" data-bs-toggle="pill" data-bs-target="#pills-collection" type="button" role="tab">Collection Popup</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-display-tab" data-bs-toggle="pill" data-bs-target="#pills-display" type="button" role="tab">Display Widget</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-items-tab" data-bs-toggle="pill" data-bs-target="#pills-items" type="button" role="tab">Manual Reviews</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-feedbacks-tab" data-bs-toggle="pill" data-bs-target="#pills-feedbacks" type="button" role="tab">Feedbacks</button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">

            <!-- COLLECTION POPUP SETTINGS -->
            <div class="tab-pane fade show active" id="pills-collection" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Popup Configuration</h5>
                        <form id="reviewsConfigForm">
                            <input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>">
                            <input type="hidden" name="active" value="<?php echo $review['active']; ?>">
                            <input type="hidden" name="show_reviews_widget" value="<?php echo $review['show_reviews_widget']; ?>">
                            <input type="hidden" name="remove_branding" value="<?php echo $review['remove_branding']; ?>">
                            <input type="hidden" name="widget_position" value="<?php echo $review['widget_position']; ?>">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Popup Title</label>
                                    <input type="text" class="form-control" name="popup_title" value="<?php echo htmlspecialchars($review['popup_title'] ?? 'Rate your experience'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" name="popup_description" value="<?php echo htmlspecialchars($review['popup_description'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Google Review Link</label>
                                    <input type="url" class="form-control" name="google_review_link" placeholder="https://g.page/..." value="<?php echo htmlspecialchars($review['google_review_link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facebook Review Link</label>
                                    <input type="url" class="form-control" name="facebook_review_link" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($review['facebook_review_link'] ?? ''); ?>">
                                </div>
                            </div>

                            <hr>

                            <h6>4-5 Star Behavior</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Action after click</label>
                                    <select class="form-select" name="high_star_action" onchange="updateActionFields('high', this.value)">
                                        <option value="thank_you" <?php echo ($review['high_star_action'] == 'thank_you') ? 'selected' : ''; ?>>Show Thank You Message</option>
                                        <option value="coupon" <?php echo ($review['high_star_action'] == 'coupon') ? 'selected' : ''; ?>>Show Coupon</option>
                                        <option value="redirect" <?php echo ($review['high_star_action'] == 'redirect') ? 'selected' : ''; ?>>Redirect to URL</option>
                                        <option value="close" <?php echo ($review['high_star_action'] == 'close') ? 'selected' : ''; ?>>Close Popup</option>
                                    </select>
                                </div>
                                <div class="col-md-8 action-field-high" id="high_thank_you" style="display:none;">
                                    <label class="form-label">Thank You Message</label>
                                    <input type="text" class="form-control" name="high_star_message" value="<?php echo htmlspecialchars($review['high_star_message'] ?? 'Thank you for your review!'); ?>">
                                </div>
                                <div class="col-md-8 action-field-high" id="high_coupon" style="display:none;">
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
                                <div class="col-md-8 action-field-high" id="high_redirect" style="display:none;">
                                    <label class="form-label">Redirect URL</label>
                                    <input type="url" class="form-control" name="high_star_redirect_url" value="<?php echo htmlspecialchars($review['high_star_redirect_url'] ?? ''); ?>">
                                </div>
                            </div>

                            <hr>

                            <h6>1-3 Star Behavior (Feedback Form)</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Action after submit</label>
                                    <select class="form-select" name="low_star_action" onchange="updateActionFields('low', this.value)">
                                        <option value="thank_you" <?php echo ($review['low_star_action'] == 'thank_you') ? 'selected' : ''; ?>>Show Thank You Message</option>
                                        <option value="coupon" <?php echo ($review['low_star_action'] == 'coupon') ? 'selected' : ''; ?>>Show Coupon</option>
                                        <option value="redirect" <?php echo ($review['low_star_action'] == 'redirect') ? 'selected' : ''; ?>>Redirect to URL</option>
                                        <option value="close" <?php echo ($review['low_star_action'] == 'close') ? 'selected' : ''; ?>>Close Popup</option>
                                    </select>
                                </div>
                                <div class="col-md-8 action-field-low" id="low_thank_you" style="display:none;">
                                    <label class="form-label">Thank You Message</label>
                                    <input type="text" class="form-control" name="low_star_message" value="<?php echo htmlspecialchars($review['low_star_message'] ?? 'Thank you for your feedback.'); ?>">
                                </div>
                                <div class="col-md-8 action-field-low" id="low_coupon" style="display:none;">
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
                                <div class="col-md-8 action-field-low" id="low_redirect" style="display:none;">
                                    <label class="form-label">Redirect URL</label>
                                    <input type="url" class="form-control" name="low_star_redirect_url" value="<?php echo htmlspecialchars($review['low_star_redirect_url'] ?? ''); ?>">
                                </div>
                            </div>

                            <hr>
                            <h6>Triggers</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Trigger Type</label>
                                    <select class="form-select" name="trigger_type">
                                        <option value="delay" <?php echo ($review['trigger_type'] == 'delay') ? 'selected' : ''; ?>>Delay</option>
                                        <option value="exit_intent" <?php echo ($review['trigger_type'] == 'exit_intent') ? 'selected' : ''; ?>>Exit Intent</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Delay (Seconds)</label>
                                    <input type="number" class="form-control" name="trigger_delay" value="<?php echo $review['trigger_delay']; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Frequency</label>
                                    <select class="form-select" name="frequency">
                                        <option value="every_load" <?php echo ($review['frequency'] == 'every_load') ? 'selected' : ''; ?>>Every Page Load</option>
                                        <option value="session" <?php echo ($review['frequency'] == 'session') ? 'selected' : ''; ?>>Once per Session</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Match URL (Optional)</label>
                                <input type="text" class="form-control" name="match_url" placeholder="e.g. /products" value="<?php echo htmlspecialchars($review['match_url'] ?? ''); ?>">
                            </div>

                            <button type="button" class="btn btn-primary" onclick="saveConfig()">Save Configuration</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DISPLAY WIDGET SETTINGS -->
            <div class="tab-pane fade" id="pills-display" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                         <h5 class="card-title">Display Widget (Toaster)</h5>
                         <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showWidgetToggle"
                                <?php echo ($review['show_reviews_widget'] ?? 0) ? 'checked' : ''; ?>
                                onchange="updateHiddenConfig('show_reviews_widget', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="showWidgetToggle">Show Reviews as Notifications</label>
                         </div>

                         <div class="mb-3">
                             <label class="form-label">Position</label>
                             <select class="form-select" id="positionSelect" onchange="updateHiddenConfig('widget_position', this.value)">
                                 <option value="bottom-left" <?php echo ($review['widget_position'] == 'bottom-left') ? 'selected' : ''; ?>>Bottom Left</option>
                                 <option value="bottom-right" <?php echo ($review['widget_position'] == 'bottom-right') ? 'selected' : ''; ?>>Bottom Right</option>
                                 <option value="top-left" <?php echo ($review['widget_position'] == 'top-left') ? 'selected' : ''; ?>>Top Left</option>
                                 <option value="top-right" <?php echo ($review['widget_position'] == 'top-right') ? 'selected' : ''; ?>>Top Right</option>
                             </select>
                         </div>

                         <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" id="brandingToggle"
                                <?php echo ($review['remove_branding'] ?? 0) ? 'checked' : ''; ?>
                                onchange="updateHiddenConfig('remove_branding', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="brandingToggle">Remove Branding</label>
                         </div>

                         <button type="button" class="btn btn-primary" onclick="saveConfig()">Save Settings</button>
                    </div>
                </div>
            </div>

            <!-- MANUAL REVIEWS -->
            <div class="tab-pane fade" id="pills-items" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Manual Reviews</h5>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addReviewModal"><i class="fa fa-plus"></i> Add Review</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Rating</th>
                                        <th>Source</th>
                                        <th>Text</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if($item['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width:24px;height:24px;border-radius:50%;margin-right:5px;">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </td>
                                        <td><?php echo $item['rating']; ?> <i class="fa fa-star text-warning"></i></td>
                                        <td>
                                            <?php if($item['source'] == 'google') echo '<i class="fab fa-google"></i> Google'; ?>
                                            <?php if($item['source'] == 'facebook') echo '<i class="fab fa-facebook"></i> Facebook'; ?>
                                            <?php if($item['source'] == 'custom') echo '<i class="fa fa-user"></i> Custom'; ?>
                                        </td>
                                        <td><small><?php echo substr(htmlspecialchars($item['review_text']), 0, 50) . '...'; ?></small></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($items)): ?>
                                    <tr><td colspan="5" class="text-center">No manual reviews added yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FEEDBACKS -->
            <div class="tab-pane fade" id="pills-feedbacks" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Collected Feedbacks (1-3 Stars)</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Rating</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedbacks as $fb): ?>
                                    <tr>
                                        <td><?php echo $fb['created_at']; ?></td>
                                        <td><?php echo $fb['rating']; ?> <i class="fa fa-star text-warning"></i></td>
                                        <td><?php echo htmlspecialchars($fb['name']); ?></td>
                                        <td><?php echo htmlspecialchars($fb['email']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($fb['feedback'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
            <div class="mb-3">
                <label class="form-label">Rating</label>
                <select class="form-select" name="rating">
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Photo URL (Optional)</label>
                <input type="url" class="form-control" name="image_url" placeholder="https://...">
            </div>
            <div class="mb-3">
                <label class="form-label">Source</label>
                <select class="form-select" name="source">
                    <option value="custom">Custom</option>
                    <option value="google">Google</option>
                    <option value="facebook">Facebook</option>
                </select>
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

function updateHiddenConfig(name, value) {
    const form = document.getElementById('reviewsConfigForm');
    form.querySelector(`input[name="${name}"]`).value = value;
}

function toggleFeature(reviewId, enabled) {
    // We reuse the updateHiddenConfig + save logic, but for the global 'active' toggle
    updateHiddenConfig('active', enabled ? 1 : 0);
    saveConfig();
}

function saveConfig() {
    const form = document.getElementById('reviewsConfigForm');
    const data = Object.fromEntries(new FormData(form).entries());

    // Checkboxes handling if needed, but we use hidden inputs updated by JS

    fetch('/campaigns/review/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            alert('Settings Saved');
            location.reload();
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
