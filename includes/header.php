<?php
// ============================================================
// header.php - Shared Navigation Header
// Included at the top of every page.
// $pageTitle variable should be set before including this file.
// ============================================================
if (!isset($pageTitle)) $pageTitle = 'KaamKhoji';

// Auto-detect base URL (works on XAMPP subdirectory or virtual host)
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}
$base = BASE_URL;

// Helper: detect profile picture for the current user (returns URL with cache-busting)
function getNavProfilePic(): ?string {
    if (!isLoggedIn()) return null;
    $uid  = getUserId();
    $root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    // Employers: prefer company logo over personal avatar if present
    if (function_exists('getRole') && getRole() === 'employer') {
        foreach (['jpg','jpeg','png','gif','webp'] as $ext) {
            $abs = $root . '/uploads/logos/' . $uid . '.' . $ext;
            if (file_exists($abs)) {
                $mtime = @filemtime($abs) ?: time();
                return BASE_URL . '/uploads/logos/' . $uid . '.' . $ext . '?v=' . $mtime;
            }
        }
    }
    // Fallback to personal avatar
    foreach (['jpg','jpeg','png','gif','webp'] as $ext) {
        $abs = $root . '/uploads/avatars/' . $uid . '.' . $ext;
        if (file_exists($abs)) {
            $mtime = @filemtime($abs) ?: time();
            return BASE_URL . '/uploads/avatars/' . $uid . '.' . $ext . '?v=' . $mtime;
        }
    }
    return null;
}
$_navPic = isLoggedIn() ? getNavProfilePic() : null;

// Fetch unread notifications for seekers and employers
$_notifCount = 0;
$_notifItems = [];
if (isLoggedIn() && in_array(getRole(), ['seeker', 'employer'])) {
    try {
        require_once __DIR__ . '/db.php';
        $_npdo = getPDO();
        $nStmt = $_npdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $nStmt->execute([getUserId()]);
        $_notifCount = (int)$nStmt->fetchColumn();

        $nItems = $_npdo->prepare("
            SELECT id, title, message, link, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $nItems->execute([getUserId()]);
        $_notifItems = $nItems->fetchAll();
    } catch (Exception $_ne) {
        // notifications are non-critical — silently skip if table not ready
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - KaamKhoji</title>

    <!-- Fontshare: Satoshi (Headline font) -->
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <!-- Google Fonts: Playfair Display (Accent serif) + Poppins (fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Page Stylesheet -->
    <link rel="stylesheet" href="<?= $base ?>/css/<?= htmlspecialchars($pageCss ?? 'landing') ?>.css">
    <!-- JS base URL for AJAX calls -->
    <script>
        const BASE_URL = '<?= $base ?>';
    </script>
    <style>
        .kk-wordmark {
            display: inline-flex;
            align-items: center;
            font-family: 'Poppins', 'Arial Black', Arial, sans-serif;
            font-weight: 900;
            font-size: 1.55rem;
            line-height: 1;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            color: #12D8E8;
            gap: 0;
        }

        .kk-wordmark .kk-glass {
            display: inline-flex;
            align-items: center;
            height: 1.18em;
            margin: 0 -0.01em;
        }

        .kk-wordmark .kk-glass svg {
            height: 100%;
            width: auto;
            display: block;
        }

        .kk-wordmark-lg {
            font-size: 2rem;
        }

        /* ---- Global nav layout — overrides all per-page CSS files ---- */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border, #e2e8f0);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-links > li {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        .nav-links a {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }

        /* ---- Logo ---- */
        .nav-logo {
            overflow: hidden;
            height: 64px; /* match navbar height — clips click area to nav bar */
            display: inline-flex;
            align-items: center;
        }

        .logo-brand-img {
            height: 165px;
            width: auto;
            display: block;
            object-fit: contain;
            /* only clip top/bottom whitespace from the PNG, never left/right */
            margin: -50px 0;
            pointer-events: none;
        }

        /* ---- Sign Up button — white background, primary text ---- */
        .nav-links a.btn-signup {
            background: #ffffff;
            color: #00b4d8;
            border: 1.5px solid #00b4d8;
            font-weight: 700;
            transition: background 0.22s ease, color 0.22s ease, box-shadow 0.22s ease;
        }

        .nav-links a.btn-signup:hover,
        .nav-links a.btn-signup:focus {
            background: #e0f7fa;
            color: #0096b3;
            box-shadow: 0 4px 16px rgba(0, 180, 216, 0.18);
        }

        /* ---- Avatar & Dropdown ---- */
        .nav-avatar-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .nav-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #00b4d8;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            font-family: "Satoshi", "Poppins", sans-serif;
        }

        .nav-avatar:hover {
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.35);
            transform: scale(1.05);
        }

        .avatar-initials {
            pointer-events: none;
        }

        .nav-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            min-width: 200px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        }

        .nav-dropdown.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .nav-dropdown-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1rem 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.07);
        }

        .dropdown-avatar-lg {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #00b4d8;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .dropdown-name {
            font-weight: 700;
            font-size: 0.88rem;
            color: #1e293b;
        }

        .dropdown-role {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: capitalize;
            margin-top: 1px;
        }

        .nav-dropdown .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.65rem 1rem;
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
            box-sizing: border-box;
        }

        .nav-dropdown .dropdown-item:hover {
            background: rgba(0, 180, 216, 0.08);
            color: #00b4d8;
        }

        .nav-dropdown .dropdown-logout {
            color: #c43c3c;
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            margin-top: 0.25rem;
            border-radius: 0 0 14px 14px;
        }

        .nav-dropdown .dropdown-logout:hover {
            background: rgba(192, 60, 60, 0.08);
            color: #a02828;
        }

        /* ---- Star / Save toggle ---- */
        .star-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .star-btn:hover {
            transform: scale(1.2);
        }

        .star-btn svg {
            width: 20px;
            height: 20px;
        }

        .star-btn .star-empty {
            display: block;
        }

        .star-btn .star-filled {
            display: none;
            color: #f59e0b;
        }

        .star-btn.starred .star-empty {
            display: none;
        }

        .star-btn.starred .star-filled {
            display: block;
        }



        /* ---- Notification Bell ---- */
        .nav-notif-wrap {
            position: relative;
            display: flex;
            align-items: center;
            margin-left: 0.25rem;
        }

        .nav-notif-btn {
            background: none;
            border: none;
            cursor: pointer;
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border-radius: 50%;
            transition: background 0.2s ease;
            color: #64748b;
            flex-shrink: 0;
            z-index: 1;
        }

        .nav-notif-btn:hover {
            background: rgba(0, 180, 216, 0.1);
            color: #00b4d8;
        }

        .notif-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            border-radius: 999px;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
            pointer-events: none;
        }

        .nav-notif-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: -8px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            width: 320px;
            max-width: 90vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            overflow: hidden;
        }

        .nav-notif-dropdown.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem 0.65rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.07);
        }

        .notif-header-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
        }

        .notif-mark-read {
            font-size: 0.75rem;
            color: #00b4d8;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            font-weight: 600;
        }

        .notif-mark-read:hover { text-decoration: underline; }

        .notif-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .notif-item {
            display: block;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            text-decoration: none;
            transition: background 0.15s ease;
            cursor: pointer;
        }

        .notif-item:last-child { border-bottom: none; }

        .notif-item:hover { background: rgba(0, 180, 216, 0.06); }

        .notif-item.unread { background: rgba(0, 180, 216, 0.06); }

        .notif-item-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.2rem;
        }

        .notif-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #00b4d8;
            flex-shrink: 0;
        }

        .notif-item-msg {
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.45;
        }

        .notif-item-time {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        .notif-empty {
            padding: 1.5rem 1rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.82rem;
        }

        /* ---- Mobile nav: avatar dropdown shows inline, not absolutely ---- */
        @media (max-width: 640px) {
            .nav-avatar-wrap {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }
            .nav-avatar {
                width: 100%;
                border-radius: 8px;
                height: auto;
                padding: 0.65rem 1rem;
                font-size: 0.9rem;
                justify-content: flex-start;
                gap: 0.6rem;
                background: var(--primary-light, rgba(0,180,216,0.1));
                color: #0096b3;
            }
            .nav-avatar::before {
                content: "Profile & Account";
                font-size: 0.9rem;
            }
            .avatar-initials { display: none; }
            .nav-dropdown {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                border: 1px solid rgba(0,0,0,0.06);
                border-radius: 8px;
                margin-top: 0.25rem;
                display: none;
                min-width: unset;
            }
            .nav-dropdown.open {
                display: block;
            }
            .nav-dropdown-header { display: none; }

            .nav-notif-dropdown {
                position: fixed;
                top: 64px;
                right: 8px;
                left: 8px;
                width: auto;
                max-width: none;
            }
        }
    </style>
</head>

<body>

    <!-- ====== NAVIGATION BAR ====== -->
    <nav class="navbar">
        <div class="nav-container">

            <!-- Logo -->
            <a href="<?= $base ?>/index.php" class="nav-logo" aria-label="KaamKhoji Home">
                <img src="<?= $base ?>/assets/kaamkhoji.png" alt="KaamKhoji" class="logo-brand-img">
            </a>

            <!-- Nav Links -->
            <ul class="nav-links" id="navLinks">
                <li><a href="<?= $base ?>/index.php">Home</a></li>
                <?php if (!isLoggedIn() || (getRole() !== 'employer' && getRole() !== 'admin')): ?>
                <li><a href="<?= $base ?>/pages/jobs.php">Find Jobs</a></li>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <!-- Links for logged-in users -->
                    <?php if (getRole() === 'seeker'): ?>
                        <li><a href="<?= $base ?>/pages/my-applications.php">My Applications</a></li>
                        <li><a href="<?= $base ?>/pages/saved-jobs.php">Saved Jobs</a></li>
                        <!-- Notification Bell -->
                        <li class="nav-notif-wrap">
                            <button class="nav-notif-btn" id="navNotifBtn" aria-label="Notifications" aria-expanded="false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                <?php if ($_notifCount > 0): ?>
                                    <span class="notif-badge" id="notifBadge"><?= min($_notifCount, 99) ?></span>
                                <?php else: ?>
                                    <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
                                <?php endif; ?>
                            </button>
                            <div class="nav-notif-dropdown" id="navNotifDropdown" role="dialog" aria-label="Notifications">
                                <div class="notif-header">
                                    <span class="notif-header-title">Notifications</span>
                                    <?php if ($_notifCount > 0): ?>
                                        <button class="notif-mark-read" id="notifMarkRead">Mark all as read</button>
                                    <?php endif; ?>
                                </div>
                                <div class="notif-list" id="notifList">
                                    <?php if (empty($_notifItems)): ?>
                                        <div class="notif-empty">No notifications yet</div>
                                    <?php else: ?>
                                        <?php foreach ($_notifItems as $_n): ?>
                                            <?php
                                                $_link = $_n['link'] ? $base . htmlspecialchars($_n['link']) : $base . '/pages/my-applications.php';
                                                $_ago  = '';
                                                $diff  = time() - strtotime($_n['created_at']);
                                                if ($diff < 60)           $_ago = 'just now';
                                                elseif ($diff < 3600)     $_ago = floor($diff/60) . 'm ago';
                                                elseif ($diff < 86400)    $_ago = floor($diff/3600) . 'h ago';
                                                else                       $_ago = date('M d', strtotime($_n['created_at']));
                                            ?>
                                            <a href="<?= $_link ?>" class="notif-item<?= !$_n['is_read'] ? ' unread' : '' ?>">
                                                <div class="notif-item-title">
                                                    <?php if (!$_n['is_read']): ?>
                                                        <span class="notif-dot"></span>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($_n['title']) ?>
                                                </div>
                                                <div class="notif-item-msg"><?= htmlspecialchars($_n['message']) ?></div>
                                                <div class="notif-item-time"><?= $_ago ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php elseif (getRole() === 'employer'): ?>
                        <li><a href="<?= $base ?>/pages/post-job.php">Post a Job</a></li>
                        <li><a href="<?= $base ?>/pages/applicants.php">Applicants</a></li>
                        <!-- Notification Bell -->
                        <li class="nav-notif-wrap">
                            <button class="nav-notif-btn" id="navNotifBtn" aria-label="Notifications" aria-expanded="false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                <?php if ($_notifCount > 0): ?>
                                    <span class="notif-badge" id="notifBadge"><?= min($_notifCount, 99) ?></span>
                                <?php else: ?>
                                    <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
                                <?php endif; ?>
                            </button>
                            <div class="nav-notif-dropdown" id="navNotifDropdown" role="dialog" aria-label="Notifications">
                                <div class="notif-header">
                                    <span class="notif-header-title">Notifications</span>
                                    <?php if ($_notifCount > 0): ?>
                                        <button class="notif-mark-read" id="notifMarkRead">Mark all as read</button>
                                    <?php endif; ?>
                                </div>
                                <div class="notif-list" id="notifList">
                                    <?php if (empty($_notifItems)): ?>
                                        <div class="notif-empty">No notifications yet</div>
                                    <?php else: ?>
                                        <?php foreach ($_notifItems as $_n): ?>
                                            <?php
                                                $_link = $_n['link'] ? $base . htmlspecialchars($_n['link']) : $base . '/pages/applicants.php';
                                                $_ago  = '';
                                                $diff  = time() - strtotime($_n['created_at']);
                                                if ($diff < 60)           $_ago = 'just now';
                                                elseif ($diff < 3600)     $_ago = floor($diff/60) . 'm ago';
                                                elseif ($diff < 86400)    $_ago = floor($diff/3600) . 'h ago';
                                                else                       $_ago = date('M d', strtotime($_n['created_at']));
                                            ?>
                                            <a href="<?= $_link ?>" class="notif-item<?= !$_n['is_read'] ? ' unread' : '' ?>">
                                                <div class="notif-item-title">
                                                    <?php if (!$_n['is_read']): ?>
                                                        <span class="notif-dot"></span>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($_n['title']) ?>
                                                </div>
                                                <div class="notif-item-msg"><?= htmlspecialchars($_n['message']) ?></div>
                                                <div class="notif-item-time"><?= $_ago ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php elseif (getRole() === 'admin'): ?>
                        <li><a href="<?= $base ?>/admin/users.php">Users</a></li>
                        <li><a href="<?= $base ?>/admin/jobs.php">Manage Jobs</a></li>
                    <?php endif; ?>

                    <li class="nav-avatar-wrap">
                        <button class="nav-avatar" id="navAvatar" aria-expanded="false" aria-label="Account menu">
                            <?php if ($_navPic): ?>
                                <img src="<?= htmlspecialchars($_navPic) ?>" alt="Profile"
                                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;" class="avatar-pic">
                            <?php else: ?>
                                <span class="avatar-initials"><?= strtoupper(mb_substr(getUserName(), 0, 1)) ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="nav-dropdown" id="navDropdown" role="menu">
                            <div class="nav-dropdown-header">
                                <div class="dropdown-avatar-lg" style="<?= $_navPic ? 'background:none;padding:0;' : '' ?>">
                                    <?php if ($_navPic): ?>
                                        <img src="<?= htmlspecialchars($_navPic) ?>" alt="Profile"
                                             style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                        <span><?= strtoupper(mb_substr(getUserName(), 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-user-info">
                                    <div class="dropdown-name"><?= htmlspecialchars(getUserName()) ?></div>
                                    <div class="dropdown-role"><?= ucfirst(getRole()) ?></div>
                                </div>
                            </div>
                            <?php if (getRole() === 'employer'): ?>
                            <a href="<?= $base ?>/pages/company-profile.php" class="dropdown-item">Profile</a>
                            <?php else: ?>
                            <a href="<?= $base ?>/pages/profile.php" class="dropdown-item">Profile</a>
                            <?php endif; ?>
                            <a href="<?= $base ?>/api/logout.php" class="dropdown-item dropdown-logout">Logout</a>
                        </div>
                    </li>

                <?php else: ?>
                    <!-- Links for guests -->
                    <li><a href="<?= $base ?>/login.php" class="btn btn-outline btn-sm">Login</a></li>
                    <li><a href="<?= $base ?>/signup.php" class="btn btn-sm btn-signup">Sign Up</a></li>
                <?php endif; ?>

            </ul>

            <!-- Hamburger for mobile -->
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Notification bell script — runs immediately so no DOMContentLoaded race -->
    <?php if (isLoggedIn() && in_array(getRole(), ['seeker', 'employer'])): ?>
    <script>
    (function () {
        var notifBtn      = document.getElementById('navNotifBtn');
        var notifDropdown = document.getElementById('navNotifDropdown');
        if (!notifBtn || !notifDropdown) return;

        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = notifDropdown.classList.toggle('open');
            notifBtn.setAttribute('aria-expanded', String(isOpen));
            // close avatar dropdown if open
            var avatarDrop = document.getElementById('navDropdown');
            if (isOpen && avatarDrop) avatarDrop.classList.remove('open');
        });

        document.addEventListener('click', function (e) {
            if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('open');
                notifBtn.setAttribute('aria-expanded', 'false');
            }
        });

        var markRead = document.getElementById('notifMarkRead');
        if (markRead) {
            markRead.addEventListener('click', function (e) {
                e.stopPropagation();
                fetch(BASE_URL + '/api/mark-notifications-read.php', { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d.success) return;
                        var badge = document.getElementById('notifBadge');
                        if (badge) badge.style.display = 'none';
                        document.querySelectorAll('.notif-item.unread').forEach(function (el) {
                            el.classList.remove('unread');
                            var dot = el.querySelector('.notif-dot');
                            if (dot) dot.remove();
                        });
                        markRead.remove();
                    }).catch(function () {});
            });
        }
    })();
    </script>
    <?php endif; ?>

    <!-- ====== FLASH MESSAGES ====== -->
    <?php if (isset($_GET['msg'])): ?>
        <?php
        $messages = [
            'login_required' => ['Login to continue.', 'warning'],
            'access_denied'  => ['Access denied.', 'error'],
            'logged_out'     => ['You have been logged out.', 'info'],
            'job_posted'     => ['Job posted successfully!', 'success'],
            'applied'        => ['Application submitted!', 'success'],
            'saved'          => ['Job saved!', 'success'],
            'deleted'        => ['Deleted successfully.', 'info'],
            'job_updated'    => ['Job status updated.', 'success'],
            'welcome'        => ['Welcome to KaamKhoji!', 'success'],
        ];
        $msg = $messages[$_GET['msg']] ?? null;
        ?>
        <?php if ($msg): ?>
            <div class="flash flash-<?= $msg[1] ?>" id="flashMsg">
                <?= htmlspecialchars($msg[0]) ?>
                <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Page content starts here -->
    <main class="main-content">