<?php
// Initialize session if not already started (though typically handled by router/index)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Dashboard';
$iframeMode = $_SESSION['iframe_mode'] ?? false;

// Ensure User Plan is Loaded
if (!isset($plan) && isset($_SESSION['user_id']) && class_exists('PlanManager')) {
    $plan = PlanManager::getUserPlan($_SESSION['user_id']);
}

// Define Features Array for Menu Generation
$features = [
    'live-conversion' => ['icon' => 'fa-cart-shopping', 'label' => 'Live Conversion', 'plan_key' => 'live_conversion'],
    'low-stock' => ['icon' => 'fa-box-open', 'label' => 'Low Stock'],
    'urgency' => ['icon' => 'fa-hourglass-half', 'label' => 'Urgency'],
    'timer' => ['icon' => 'fa-clock', 'label' => 'Timer'],
    'reviews' => ['icon' => 'fa-star', 'label' => 'Reviews', 'plan_key' => 'reviews'],
    'live-visitors' => ['icon' => 'fa-users', 'label' => 'Live Visitors', 'plan_key' => 'live_visitor'],
    'in-line-text' => ['icon' => 'fa-font', 'label' => 'In-Line Text'],
    'callback' => ['icon' => 'fa-phone', 'label' => 'Callback'],
    'social' => ['icon' => 'fa-share-nodes', 'label' => 'Social', 'plan_key' => 'socials'],
    'coupon' => ['icon' => 'fa-ticket', 'label' => 'Coupon', 'plan_key' => 'coupons'],
    'video' => ['icon' => 'fa-video', 'label' => 'Video', 'plan_key' => 'videos'],
    'announcement' => ['icon' => 'fa-bullhorn', 'label' => 'Announcement', 'plan_key' => 'notifications'],
    'newsletter' => ['icon' => 'fa-envelope', 'label' => 'Newsletter', 'plan_key' => 'newsletters'],
];

// Helper to generate a nav item
function renderNavItem($url, $icon, $label, $activeCondition, $iframeMode) {
    $activeClass = $activeCondition ? 'active' : '';
    // For iframe mode, we might want fewer styles or specific classes, but style.css handles .top-navbar .nav-link vs .sidebar .nav-link
    return "
    <li class='nav-item'>
        <a href='{$url}' class='nav-link {$activeClass}'>
            <i class='fa-solid {$icon}'></i>
            " . ($iframeMode ? "<span class='d-lg-none d-xl-block ms-2'>{$label}</span>" : "<span class='ms-2'>{$label}</span>") . "
             " . ($iframeMode ? "<span class='d-none d-lg-inline ms-2'>{$label}</span>" : "") . "
        </a>
    </li>";
}
// Actually, simple text rendering is better, let CSS handle visibility if needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trustabee - <?php echo $pageTitle; ?></title>

    <!-- Google Fonts: Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">

    <!-- Pickr CSS (Color Picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/>
</head>
<body class="<?php echo $iframeMode ? 'iframe-mode' : ''; ?>">

<?php if ($iframeMode): ?>
    <!-- Iframe Mode: Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fa-solid fa-layer-group text-primary me-2"></i> Trustabee
            </a>

            <!-- Mobile Toggle (Hamburger on Right) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Desktop Menu (Horizontal) -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>" href="/">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($activePage == 'campaigns') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Campaigns
                        </a>
                        <ul class="dropdown-menu border-0 shadow">
                             <?php foreach ($features as $key => $feature):
                                $isSubActive = ($activeSubPage ?? '') === $key;
                            ?>
                            <li><a class="dropdown-item <?php echo $isSubActive ? 'active' : ''; ?>" href="/campaigns/<?php echo $key; ?>">
                                <i class="fa-solid <?php echo $feature['icon']; ?> me-2"></i> <?php echo $feature['label']; ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activePage == 'billing') ? 'active' : ''; ?>" href="/billing">Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activePage == 'settings') ? 'active' : ''; ?>" href="/settings">Settings</a>
                    </li>
                     <?php if (($_SESSION['role'] ?? 'user') === 'owner'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item" href="/admin/users">Manage Users</a></li>
                            <li><a class="dropdown-item" href="/admin/plans">Manage Plans</a></li>
                            <li><a class="dropdown-item" href="/admin/tokens">Partner Tokens</a></li>
                            <li><a class="dropdown-item" href="/admin/email">Manage Email</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Offcanvas Menu (Right Side) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold" id="offcanvasRightLabel">
                <i class="fa-solid fa-layer-group text-primary me-2"></i> Trustabee
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>" href="/"><i class="fa-solid fa-house me-2"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#mobileCampaigns" role="button" aria-expanded="false">
                        <i class="fa-solid fa-bullhorn me-2"></i> Campaigns <i class="fa-solid fa-chevron-down ms-auto" style="font-size: 0.8rem"></i>
                    </a>
                    <div class="collapse" id="mobileCampaigns">
                        <ul class="list-unstyled ps-3">
                        <?php foreach ($features as $key => $feature): ?>
                            <li><a class="nav-link py-2" href="/campaigns/<?php echo $key; ?>"><?php echo $feature['label']; ?></a></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/billing"><i class="fa-solid fa-credit-card me-2"></i> Billing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/settings"><i class="fa-solid fa-gear me-2"></i> Settings</a>
                </li>
                <!-- No Logout for Iframe Mode as requested -->
            </ul>
        </div>
    </div>

<?php else: ?>
    <!-- Standard Mode: Sidebar -->
    <!-- Mobile Toggle for Sidebar -->
    <div class="d-lg-none p-3 position-fixed w-100 bg-white shadow-sm" style="z-index: 1001; top:0;">
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="ms-3 fw-bold">Trustabee</span>
    </div>

    <!-- Desktop Sidebar (Hidden on Mobile) -->
    <aside class="sidebar d-none d-lg-flex">
        <div class="sidebar-brand">
            <i class="fa-solid fa-layer-group"></i> Trustabee
        </div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item">
                <a href="/" class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a href="#campaignsSubmenu" class="nav-link <?php echo ($activePage == 'campaigns') ? 'active' : ''; ?>" data-bs-toggle="collapse">
                    <i class="fa-solid fa-bullhorn"></i> Campaigns
                    <i class="fa-solid fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                </a>
                <div class="collapse <?php echo ($activePage == 'campaigns') ? 'show' : ''; ?>" id="campaignsSubmenu">
                    <ul class="submenu list-unstyled">
                        <?php foreach ($features as $key => $feature):
                            $isSubActive = ($activeSubPage ?? '') === $key;
                            $planKey = $feature['plan_key'] ?? null;
                            $isLocked = $planKey && empty($plan['features'][$planKey]);

                            // Check if unimplemented
                            $implemented = in_array($key, ['live-conversion', 'live-visitors', 'coupon', 'announcement', 'video', 'newsletter', 'social', 'reviews']);
                        ?>
                        <li>
                            <a href="/campaigns/<?php echo $key; ?>" class="nav-link <?php echo $isSubActive ? 'active' : ''; ?>">
                                <?php echo $feature['label']; ?>
                                <?php if($isLocked): ?>
                                    <span class="badge-locked"><i class="fa-solid fa-lock"></i> Locked</span>
                                <?php elseif(!$implemented): ?>
                                    <span class="badge-demo">Demo</span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="/billing" class="nav-link <?php echo ($activePage == 'billing') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-credit-card"></i> Billing
                </a>
            </li>
            <li class="nav-item">
                <a href="/settings" class="nav-link <?php echo ($activePage == 'settings') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </li>

            <?php if (($_SESSION['role'] ?? 'user') === 'owner'): ?>
            <li class="nav-item mt-3">
                <div class="text-uppercase text-muted fw-bold px-4 small mb-2">Super Admin</div>
            </li>
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?php echo ($activePage == 'admin' && $activeSubPage == 'users') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users-gear"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/plans" class="nav-link <?php echo ($activePage == 'admin' && $activeSubPage == 'plans') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-list"></i> Manage Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/tokens" class="nav-link <?php echo ($activePage == 'admin' && $activeSubPage == 'tokens') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-key"></i> Partner Tokens
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/email" class="nav-link <?php echo ($activePage == 'admin' && $activeSubPage == 'email') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-envelope"></i> Manage Email
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="p-3 mt-auto">
             <a href="/logout" class="nav-link text-danger">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold">Trustabee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
             <!-- Reuse same nav structure or simplified one -->
             <ul class="nav flex-column sidebar-nav">
                <!-- ... Repeat content or use PHP include if extracted ... -->
                <!-- Copying logic for simplicity in this single file edit -->
                <li class="nav-item"><a href="/" class="nav-link"><i class="fa-solid fa-house"></i> Home</a></li>
                <li class="nav-item">
                    <a href="#mobCamp" class="nav-link" data-bs-toggle="collapse">
                        <i class="fa-solid fa-bullhorn"></i> Campaigns
                    </a>
                    <div class="collapse" id="mobCamp">
                        <ul class="submenu list-unstyled">
                             <?php foreach ($features as $key => $feature): ?>
                                <li><a href="/campaigns/<?php echo $key; ?>" class="nav-link"><?php echo $feature['label']; ?></a></li>
                             <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <li class="nav-item"><a href="/billing" class="nav-link"><i class="fa-solid fa-credit-card"></i> Billing</a></li>
                <li class="nav-item"><a href="/settings" class="nav-link"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li class="nav-item"><a href="/logout" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </div>

<?php endif; ?>

<main class="main-content">
    <?php if(!$iframeMode): ?>
    <div class="d-none d-lg-block mb-4">
        <h1 class="h3 fw-bold text-dark mb-0"><?php echo $pageTitle; ?></h1>
    </div>
    <!-- Mobile Spacer -->
    <div class="d-lg-none" style="height: 60px;"></div>
    <?php endif; ?>
