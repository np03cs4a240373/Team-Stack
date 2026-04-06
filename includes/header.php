<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - KaamKhoji</title>

    <!-- Satoshi font -->
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- page stylesheet -->
    <link rel="stylesheet" href="/css/landing.css">

    <!-- base url used by JS for ajax calls -->
    <script>
        const BASE_URL = '';
    </script>

    <style>
        /* logo wordmark styling */
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

        /* logo image has white padding so we use negative margin to crop it */
        .nav-logo {
            overflow: visible;
        }

        .logo-brand-img {
            height: 165px;
            width: auto;
            display: block;
            object-fit: contain;
            margin: -34px -24px;
        }

        /* signup button is white with blue border */
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

        /* wrapper for avatar button and dropdown */
        .nav-avatar-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* round blue circle button showing user initial */
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

        /* dropdown is hidden by default */
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
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        }

        /* adding open class makes dropdown visible */
        .nav-dropdown.open {
            opacity: 1;
            visibility: visible;
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

        .dropdown-item {
            display: block;
            padding: 0.65rem 1rem;
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: rgba(0, 180, 216, 0.08);
            color: #00b4d8;
        }

        /* logout is red and sits at the bottom of dropdown */
        .dropdown-logout {
            color: #ef4444;
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            margin-top: 0.25rem;
            border-radius: 0 0 14px 14px;
        }

        .dropdown-logout:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
        }

        /* star button to save a job */
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

        /* empty star shows when job is not saved */
        .star-btn .star-empty {
            display: block;
        }

        /* filled star shows when job is saved */
        .star-btn .star-filled {
            display: none;
            color: #f59e0b;
        }

        /* when starred class is added, swap the icons */
        .star-btn.starred .star-empty {
            display: none;
        }

        .star-btn.starred .star-filled {
            display: block;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">

            <!-- logo links to home page -->
            <a href="/index.php" class="nav-logo" aria-label="KaamKhoji Home">
                <img src="/assets/kaamkhoji.png" alt="KaamKhoji" class="logo-brand-img">
            </a>

            <!-- nav menu links -->
            <ul class="nav-links" id="navLinks">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/pages/jobs.php">Find Jobs</a></li>

                <!-- seeker links - shown when user role is seeker -->
                <li><a href="/pages/my-applications.php">My Applications</a></li>
                <li><a href="/pages/saved-jobs.php">Saved Jobs</a></li>

                <!-- employer links - swap with above when role is employer -->
                <!-- <li><a href="/pages/post-job.php">Post a Job</a></li> -->
                <!-- <li><a href="/pages/applicants.php">Applicants</a></li> -->

                <!-- admin links - swap with above when role is admin -->
                <!-- <li><a href="/admin/users.php">Users</a></li> -->
                <!-- <li><a href="/admin/jobs.php">Manage Jobs</a></li> -->

                <!-- avatar button and dropdown - shown when user is logged in -->
                <li class="nav-avatar-wrap">
                    <button class="nav-avatar" id="navAvatar" aria-expanded="false" aria-label="Account menu">
                        <span class="avatar-initials">J</span>
                    </button>
                    <div class="nav-dropdown" id="navDropdown" role="menu">
                        <div class="nav-dropdown-header">
                            <div class="dropdown-avatar-lg">
                                <span>J</span>
                            </div>
                            <div class="dropdown-user-info">
                                <div class="dropdown-name">John Doe</div>
                                <div class="dropdown-role">Seeker</div>
                            </div>
                        </div>
                        <a href="/pages/profile.php" class="dropdown-item">Profile</a>
                        <a href="/api/logout.php" class="dropdown-item dropdown-logout">Logout</a>
                    </div>
                </li>

                <!-- guest links - shown when user is NOT logged in -->
                <!-- <li><a href="/login.php" class="btn btn-outline btn-sm">Login</a></li> -->
                <!-- <li><a href="/signup.php" class="btn btn-sm btn-signup">Sign Up</a></li> -->

            </ul>

            <!-- hamburger button for mobile menu -->
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- FLASH MESSAGE - shows a short notification to the user -->
    <div class="flash flash-success" id="flashMsg">
        Job posted successfully!
        <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
    </div>

    <!-- page content goes inside here -->
    <main class="main-content">