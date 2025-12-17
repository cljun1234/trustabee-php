<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trustabee - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0084ff; /* ManyChat blue-ish */
            --bg-color: #f6f7f9;
            --text-color: #354052;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f0f2f5;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid #e1e4e8;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            z-index: 100;
        }
        .logo-area {
            padding: 20px 24px;
            font-weight: 800;
            font-size: 1.4rem;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-area i { color: var(--primary-color); }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        .nav-item {
            margin-bottom: 2px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: #4a5568;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--primary-color);
        }
        .nav-link.active {
            background: #e6f2ff;
            color: var(--primary-color);
            border-right: 3px solid var(--primary-color);
        }
        .nav-link i {
            width: 24px;
            margin-right: 10px;
            text-align: center;
        }

        /* Submenu */
        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: #fcfcfc;
            display: none; /* Hidden by default */
        }
        .submenu.open {
            display: block;
        }
        .submenu .nav-link {
            padding-left: 58px; /* Indent */
            font-size: 0.9rem;
        }

        .badge-demo {
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: auto;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            padding: 30px 40px;
            overflow-y: auto;
        }

        /* Top Bar in Main Content (Optional, for greeting/profile) */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }

        /* Common Components from original dashboard */
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .stat-box { background: #e8f0fe; color: #1a73e8; padding: 1rem; border-radius: 8px; text-align: center; font-size: 1.5rem; font-weight: bold; display: inline-block; min-width: 100px; }
        .stat-label { font-size: 0.8rem; font-weight: normal; display: block; color: #555; }
        textarea.code-block { width: 100%; height: 60px; font-family: monospace; padding: 10px; background: #2d2d2d; color: #f8f8f2; border-radius: 4px; border: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; }
        th { color: #666; font-size: 0.9rem; }
        .btn { padding: 8px 16px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn-sm { padding: 4px 8px; font-size: 0.8rem; background: #dc3545; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        .toggle { display: flex; align-items: center; }
        .toggle input { margin-right: 10px; }

        /* Utility */
        .text-muted { color: #6c757d; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-area">
        <i class="fa-solid fa-layer-group"></i> Trustabee
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="/" class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i> Home
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link <?php echo ($activePage == 'campaigns') ? 'active' : ''; ?>" onclick="toggleSubmenu('campaigns-submenu'); return false;">
                <i class="fa-solid fa-bullhorn"></i> Campaigns
                <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; margin-left: auto; width: auto;"></i>
            </a>
            <ul id="campaigns-submenu" class="submenu <?php echo ($activePage == 'campaigns') ? 'open' : ''; ?>">
                <?php
                $features = [
                    'live-conversion' => ['icon' => 'fa-cart-shopping', 'label' => 'Live Conversion'],
                    'low-stock' => ['icon' => 'fa-box-open', 'label' => 'Low Stock'],
                    'urgency' => ['icon' => 'fa-hourglass-half', 'label' => 'Urgency'],
                    'timer' => ['icon' => 'fa-clock', 'label' => 'Timer'],
                    'reviews' => ['icon' => 'fa-star', 'label' => 'Reviews'],
                    'live-visitors' => ['icon' => 'fa-users', 'label' => 'Live Visitors'],
                    'in-line-text' => ['icon' => 'fa-font', 'label' => 'In-Line Text'],
                    'callback' => ['icon' => 'fa-phone', 'label' => 'Callback'],
                    'social' => ['icon' => 'fa-share-nodes', 'label' => 'Social'],
                    'coupon' => ['icon' => 'fa-ticket', 'label' => 'Coupon'],
                    'video' => ['icon' => 'fa-video', 'label' => 'Video'],
                    'announcement' => ['icon' => 'fa-bullhorn', 'label' => 'Announcement'],
                    'newsletter' => ['icon' => 'fa-envelope', 'label' => 'Newsletter'],
                ];

                foreach ($features as $key => $feature):
                    $isSubActive = ($activeSubPage ?? '') === $key;
                ?>
                <li>
                    <a href="/campaigns/<?php echo $key; ?>" class="nav-link <?php echo $isSubActive ? 'active' : ''; ?>" style="<?php echo $isSubActive ? 'border-right: none; background: #f0f7ff; color: var(--primary-color); font-weight: 600;' : ''; ?>">
                        <i class="fa-solid <?php echo $feature['icon']; ?>"></i>
                        <?php echo $feature['label']; ?>
                        <?php if(!in_array($key, ['live-conversion', 'live-visitors', 'coupon', 'announcement', 'video', 'newsletter', 'social', 'reviews'])): ?><span class="badge-demo">Demo</span><?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
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
        <li class="nav-item" style="margin-top: 10px; border-top: 1px solid #eee;">
            <div style="padding: 10px 24px; font-size: 0.75rem; text-transform: uppercase; color: #999; font-weight: bold;">Super Admin</div>
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
        <?php endif; ?>
    </ul>

    <div style="margin-top: auto; padding: 20px;">
         <a href="/logout" class="nav-link" style="color: #dc3545;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        <!-- User profile or status could go here -->
    </div>
