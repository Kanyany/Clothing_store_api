<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Admin Panel')
    </title>

    <style>
        /* =========================================================
           GLOBAL
        ========================================================= */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #FAF8F5;
            color: #24201E;
        }


        /* =========================================================
           ADMIN WRAPPER
        ========================================================= */

        .admin-wrapper {
            min-height: 100vh;

            display: flex;

            gap: 24px;

            padding: 20px;
        }


        /* =========================================================
           ADMIN MAIN
        ========================================================= */

        .admin-main {
            flex: 1;

            min-width: 0;

            min-height: calc(100vh - 40px);
        }


        /* =========================================================
           ADMIN CONTENT
        ========================================================= */

        .admin-content {
            width: 100%;

            padding: 10px 10px 30px;
        }


        /* =========================================================
           SIDEBAR
        ========================================================= */

        .admin-sidebar {
            width: 250px;

            min-height: calc(100vh - 40px);

            flex-shrink: 0;

            background: #2C1E17;

            border-radius: 24px;

            padding: 24px 16px;

            display: flex;

            flex-direction: column;

            box-shadow:
                0 10px 30px rgba(44, 30, 23, 0.08);
        }


        /* =========================================================
           SIDEBAR PROFILE
        ========================================================= */

        .sidebar-profile {
            padding: 10px 8px 28px;

            text-align: center;
        }


        .profile-avatar {
            width: 64px;

            height: 64px;

            margin: 0 auto 12px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #C99A6B;

            color: #FAF8F5;

            font-size: 24px;

            font-weight: 700;
        }


        .profile-info h3 {
            margin: 0;

            color: #FAF8F5;

            font-size: 15px;

            font-weight: 600;
        }


        .profile-info p {
            margin: 5px 0 0;

            color: #C99A6B;

            font-size: 11px;

            word-break: break-word;
        }


        /* =========================================================
           SIDEBAR NAVIGATION
        ========================================================= */

        .sidebar-navigation {
            flex: 1;
        }


        .sidebar-menu,
        .logout-button {
            width: 100%;

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 14px;

            margin-bottom: 6px;

            border: none;

            border-radius: 12px;

            background: transparent;

            color: #F5EDE3;

            text-decoration: none;

            font-size: 13px;

            cursor: pointer;

            transition:
                background 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }


        .sidebar-menu:hover,
        .logout-button:hover {
            background: #6F4E37;

            color: #FAF8F5;
        }


        /* =========================================================
           ACTIVE MENU
        ========================================================= */

        .sidebar-menu.active {
            background: #F5EDE3;

            color: #2C1E17;
        }


        .sidebar-menu.active .menu-icon {
            color: #6F4E37;
        }


        /* =========================================================
           MENU ICON
        ========================================================= */

        .menu-icon {
            width: 22px;

            height: 22px;

            flex-shrink: 0;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            font-size: 16px;
        }


        /* =========================================================
           MENU LABEL
        ========================================================= */

        .menu-label {
            flex: 1;

            text-align: left;
        }


        /* =========================================================
           SIDEBAR FOOTER
        ========================================================= */

        .sidebar-footer {
            padding-top: 16px;
        }


        .logout-button {
            color: #C99A6B;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1100px) {

            .admin-wrapper {
                gap: 16px;

                padding: 16px;
            }

            .admin-sidebar {
                width: 220px;

                min-height: calc(100vh - 32px);
            }

        }


        @media (max-width: 700px) {

            .admin-wrapper {
                flex-direction: column;

                gap: 12px;

                padding: 12px;
            }


            .admin-sidebar {
                width: 100%;

                min-height: auto;

                border-radius: 20px;
            }


            .sidebar-navigation {
                display: grid;

                grid-template-columns: repeat(2, 1fr);

                gap: 6px;
            }


            .sidebar-menu {
                margin-bottom: 0;
            }


            .sidebar-footer {
                margin-top: 10px;
            }


            .admin-main {
                min-height: auto;
            }


            .admin-content {
                padding: 10px 0 24px;
            }

        }


        /* =========================================================
           EXTRA SMALL SCREEN
        ========================================================= */

        @media (max-width: 450px) {

            .sidebar-navigation {
                grid-template-columns: 1fr;
            }

        }
                /* =========================================
        ADMIN NAVBAR
        ========================================= */

        .admin-navbar {
            width: 100%;
            min-height: 82px;

            display: flex;
            align-items: center;

            background: #FAF8F5;

            padding: 0 28px;
            margin-bottom: 24px;

            border-bottom: 1px solid rgba(44, 30, 23, 0.08);

            box-sizing: border-box;
        }

        /* Brand */

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;

            min-width: 220px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #6F4E37;
            color: #F5EDE3;

            border-radius: 12px;

            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-name {
            color: #24201E;

            font-size: 15px;
            font-weight: 800;
            line-height: 1.2;
        }

        .brand-subtitle {
            color: #8B8580;

            font-size: 11px;
            font-weight: 500;
        }

        /* Page title */

        .navbar-page {
            flex: 1;

            display: flex;
            align-items: center;

            padding-left: 28px;
        }

        .page-label {
            color: #24201E;

            font-size: 20px;
            font-weight: 700;
        }

        /* Actions */

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* Search */

        .navbar-search {
            width: 210px;
            height: 42px;

            display: flex;
            align-items: center;

            background: #FFFFFF;

            border: 1px solid rgba(44, 30, 23, 0.10);

            border-radius: 12px;

            padding: 0 12px;

            box-sizing: border-box;
        }

        .search-icon {
            color: #8B8580;

            font-size: 20px;

            margin-right: 8px;
        }

        .navbar-search input {
            width: 100%;

            border: none;
            outline: none;

            background: transparent;

            color: #24201E;

            font-size: 13px;
        }

        .navbar-search input::placeholder {
            color: #A39D98;
        }

        /* Notification */

        .navbar-icon-button {
            position: relative;

            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #FFFFFF;

            border: 1px solid rgba(44, 30, 23, 0.10);

            border-radius: 12px;

            color: #6F4E37;

            font-size: 20px;

            cursor: pointer;
        }

        .notification-dot {
            position: absolute;

            top: 9px;
            right: 9px;

            width: 6px;
            height: 6px;

            background: #C85C4A;

            border-radius: 50%;
        }

        /* User */

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 10px;

            padding-left: 4px;
        }

        .navbar-avatar {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #C99A6B;
            color: #FFFFFF;

            border-radius: 50%;

            font-size: 15px;
            font-weight: 700;
        }

        .navbar-user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .navbar-user-name {
            color: #24201E;

            font-size: 13px;
            font-weight: 700;
        }

        .navbar-user-role {
            color: #8B8580;

            font-size: 11px;
        }

        /* =========================================
        RESPONSIVE
        ========================================= */

        @media (max-width: 1100px) {

            .navbar-brand {
                min-width: 180px;
            }

            .navbar-search {
                width: 160px;
            }

            .navbar-user-info {
                display: none;
            }
        }

        @media (max-width: 800px) {

            .admin-navbar {
                padding: 0 18px;
            }

            .navbar-brand {
                min-width: auto;
            }

            .brand-text {
                display: none;
            }

            .navbar-page {
                padding-left: 18px;
            }

            .navbar-search {
                display: none;
            }
        }

    </style>

    @stack('styles')
</head>


<body>

    <div class="admin-wrapper">

        {{-- =====================================================
             SIDEBAR
        ====================================================== --}}

        @include('admin.layouts.sidebar')


        {{-- =====================================================
             MAIN CONTENT
        ====================================================== --}}

        <main class="admin-main">

            @include('admin.layouts.navbar')

            <div class="admin-content">

                @yield('content')

            </div>

        </main>

    </div>


    @stack('scripts')

</body>
</html>