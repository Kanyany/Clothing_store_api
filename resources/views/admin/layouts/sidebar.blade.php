@php
  
    $sidebarMenus = [
        ['label' => 'Overview',      'icon' => 'layout-dashboard', 'route' => 'admin.dashboard',             'active' => 'admin.dashboard'],
        ['label' => 'Products',      'icon' => 'package',          'route' => 'admin.products.index',        'active' => 'admin.products.*'],
        ['label' => 'Categories',    'icon' => 'grid-2x2',          'route' => 'admin.categories.index',      'active' => 'admin.categories.*'],
        ['label' => 'Inventory',     'icon' => 'warehouse',        'route' => 'admin.inventory.index',       'active' => 'admin.inventory.*'],
        ['label' => 'Notifications', 'icon' => 'bell',              'route' => 'admin.notifications.index',   'active' => 'admin.notifications.*', 'badge' => $unreadNotifications ?? 0],
        ['label' => 'Purchases',     'icon' => 'clipboard-list',    'route' => 'admin.purchases.index',       'active' => 'admin.purchases.*'],
        ['label' => 'Orders',        'icon' => 'clipboard-list',    'route' => 'admin.orders.index',          'active' => 'admin.orders.*'],
        ['label' => 'Sale',          'icon' => 'shopping-cart',     'route' => 'admin.sale.index',            'active' => 'admin.sale.*'],
        ['label' => 'Banners', 'icon' => 'image', 'route' => 'admin.banners.index', 'active' => 'admin.banners.*'],        ['label' => 'Collections',   'icon' => 'layers',            'route' => 'admin.collections.index',     'active' => 'admin.collections.*'],
        ['label' => 'Reports',       'icon' => 'chart-column',      'route' => 'admin.reports.index',          'active' => 'admin.reports.*'],
        ['label' => 'Users & Roles', 'icon' => 'users',             'route' => 'admin.users.index',           'active' => 'admin.users.*'],

    ];

@endphp

<style>
    :root {
        --aw-sidebar-width: 260px;
        --aw-sidebar-width-compact: 76px;
    }

    /* ============ BASE (Desktop > 1100px) ============ */
    .aw-sidebar {
        width: var(--aw-sidebar-width);
        min-width: var(--aw-sidebar-width);
        flex-shrink: 0;
        height: 100vh;
        height: 100dvh;
        position: sticky;
        top: 0;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-right: 1px solid #e9e9ed;
        font-family: Inter, Arial, sans-serif;
        overflow: hidden;
        z-index: 100;
    }

    .aw-sidebar *,
    .aw-sidebar *::before,
    .aw-sidebar *::after {
        box-sizing: border-box;
    }

    .aw-sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 26px 22px 22px;
    }

    .aw-brand-icon {
        width: 43px;
        height: 43px;
        flex-shrink: 0;
        border-radius: 13px;
        background: #242326;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
    }

    .aw-brand-name {
        margin: 0;
        color: #242326;
        font-size: 16px;
        font-weight: 750;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .aw-brand-subtitle {
        color: #92919a;
        font-size: 11px;
        margin-top: 3px;
        white-space: nowrap;
    }

    /* Shared look for the open (toggle) and close buttons */
    .aw-mobile-toggle,
    .aw-sidebar-close {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        padding: 0;
        border: 1px solid #e3e3e7;
        border-radius: 12px;
        background: #fff;
        color: #242326;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: background .15s ease;
    }

    .aw-mobile-toggle:hover,
    .aw-sidebar-close:hover {
        background: #f5f4f2;
    }

    .aw-mobile-toggle svg,
    .aw-sidebar-close svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* hidden on desktop + tablet */
    .aw-mobile-bar,
    .aw-mobile-toggle,
    .aw-sidebar-close,
    .aw-mobile-overlay {
        display: none;
    }

    .aw-sidebar-navigation {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0 13px 16px;
    }

    .aw-nav-heading {
        padding: 0 12px;
        margin: 0 0 12px;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: 1.5px;
        color: #aaa9b1;
    }

    .aw-sidebar-menu {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 45px;
        padding: 0 13px;
        margin-bottom: 5px;
        border-radius: 10px;
        text-decoration: none;
        color: #686873;
        font-size: 13px;
        font-weight: 550;
        white-space: nowrap;
        transition: background .18s ease, color .18s ease;
    }

    .aw-sidebar-menu:hover {
        background: #f5f4f2;
        color: #242326;
        text-decoration: none;
    }

    .aw-sidebar-menu.active {
        background: #f0ece7;
        color: #26211c;
        font-weight: 700;
    }

    .aw-sidebar-menu:focus-visible,
    .aw-logout-button:focus-visible,
    .aw-mobile-toggle:focus-visible,
    .aw-sidebar-close:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }

    .aw-menu-icon {
        width: 21px;
        height: 21px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .aw-menu-icon svg {
        width: 19px;
        height: 19px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* Unread badge on Notifications / Messages */
    .aw-menu-badge {
        margin-left: auto;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #d9534f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* ---- Footer: profile row + logout (profile now lives at the bottom) ---- */
    .aw-sidebar-footer {
        border-top: 1px solid #ededf0;
        padding: 12px 13px 16px;
    }

    .aw-sidebar-profile {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 6px;
        padding: 10px;
        border: 1px solid #ededf0;
        border-radius: 13px;
        background: #fafafa;
    }

    .aw-avatar {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: 11px;
        background: #eae6e0;
        color: #51483e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .aw-profile-info { min-width: 0; }

    .aw-profile-name {
        margin: 0;
        font-size: 13px;
        font-weight: 650;
        color: #29292c;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aw-profile-email {
        display: block;
        color: #96969e;
        font-size: 11px;
        margin-top: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aw-logout-button {
        display: flex;
        align-items: center;
        gap: 13px;
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 10px;
        padding: 0 13px;
        background: transparent;
        color: #a34747;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        text-align: left;
    }

    .aw-logout-button:hover { background: #fff0f0; }

    /* ============ TABLET (769px - 1100px): compact icon rail ============ */
    @media (min-width: 769px) and (max-width: 1100px) {
        .aw-sidebar {
            width: var(--aw-sidebar-width-compact);
            min-width: var(--aw-sidebar-width-compact);
        }

        .aw-sidebar-brand {
            justify-content: center;
            padding: 22px 8px;
        }

        .aw-brand-text,
        .aw-profile-info,
        .aw-nav-heading,
        .aw-menu-label {
            display: none;
        }

        .aw-sidebar-navigation { padding: 8px 10px 16px; }
        .aw-sidebar-footer { padding: 12px 10px 16px; }

        .aw-sidebar-profile {
            justify-content: center;
            padding: 6px 0;
            border: 0;
            background: transparent;
        }

        .aw-sidebar-menu,
        .aw-logout-button {
            justify-content: center;
            gap: 0;
            padding: 0;
        }

        /* badge becomes a small count on the icon corner in the icon rail */
        .aw-menu-badge {
            position: absolute;
            top: 5px;
            right: 9px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            font-size: 9px;
            margin: 0;
        }
    }

    /* ============ MOBILE (<= 768px): toggle button + slide-in drawer ============ */
    @media (max-width: 768px) {
        .aw-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: min(320px, 88vw);
            min-width: 0;
            height: 100dvh;
            transform: translateX(-100%);
            visibility: hidden;
            transition: transform .3s cubic-bezier(.32, .72, 0, 1), visibility .3s;
            will-change: transform;
            z-index: 1100;
        }

        body.aw-sidebar-open .aw-sidebar {
            transform: translateX(0);
            visibility: visible;
            box-shadow: 8px 0 30px rgba(0, 0, 0, .14);
        }

        /* Header row: [X] ALWAY WIN  — X sits exactly where the toggle was */
        .aw-sidebar-brand {
            gap: 12px;
            padding: 10px 12px 14px;
        }

        .aw-sidebar-close { display: flex; }
        .aw-brand-icon { display: none; }

        .aw-brand-name {
            font-size: 17px;
            line-height: 1.1;
        }

        .aw-sidebar-menu {
            min-height: 48px;
            font-size: 14px;
        }

        /* Slim top bar that holds the toggle, so it never covers page content */
        .aw-mobile-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            padding: 0 12px;
            background: #ffffff;
            border-bottom: 1px solid #e9e9ed;
            font-family: Inter, Arial, sans-serif;
            z-index: 1050;
        }

        .aw-mobile-toggle { display: flex; }

        .aw-mobile-bar-title {
            color: #242326;
            font-size: 15px;
            font-weight: 750;
            letter-spacing: 1px;
        }

        /* push the page below the bar */
        body { padding-top: 60px; }

        /* stop the page jumping sideways when the scrollbar disappears */
        html { scrollbar-gutter: stable; }

        .aw-sidebar-navigation { overscroll-behavior: contain; }

        .aw-mobile-overlay {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 1090;
            background: rgba(0, 0, 0, .4);
            opacity: 0;
            visibility: hidden;
            transition: opacity .3s ease, visibility .3s;
        }

        body.aw-sidebar-open .aw-mobile-overlay {
            opacity: 1;
            visibility: visible;
        }

        body.aw-sidebar-open { overflow: hidden; }
    }

    @media (prefers-reduced-motion: reduce) {
        .aw-sidebar,
        .aw-mobile-overlay,
        .aw-sidebar-menu { transition: none; }
    }

    /* ALWAY WIN — Brown & Cream theme overrides */
    :root {
        --aw-cream: #FAF8F5;
        --aw-brown: #70513D;
        --aw-beige: #F0E7DC;
        --aw-dark: #242326;
        --aw-border: #E6D9CB;
        --aw-accent: #B87345;
    }

    .aw-sidebar {
        background: var(--aw-cream);
        border-right-color: var(--aw-border);
    }
    .aw-brand-icon {
        background: var(--aw-brown);
        color: var(--aw-cream);
    }
    .aw-brand-name, .aw-profile-name { color: var(--aw-dark); }
    .aw-brand-subtitle, .aw-profile-email { color: #8D7666; }
    .aw-nav-heading { color: #94745E; }
    .aw-sidebar-menu { color: #5B4538; }
    .aw-sidebar-menu:hover {
        background: var(--aw-beige);
        color: var(--aw-brown);
    }
    .aw-sidebar-menu.active,
    .aw-sidebar-menu.active:hover {
        background: var(--aw-brown);
        color: var(--aw-cream);
        box-shadow: 0 3px 10px rgba(112, 81, 61, .15);
    }
    .aw-sidebar-footer { border-top-color: var(--aw-border); }
    .aw-sidebar-profile {
        background: #F5EEE6;
        border-color: var(--aw-border);
    }
    .aw-avatar {
        background: var(--aw-brown);
        color: var(--aw-cream);
    }
    .aw-logout-button { color: var(--aw-brown); }
    .aw-logout-button:hover { background: var(--aw-beige); }
    .aw-mobile-toggle, .aw-sidebar-close {
        background: var(--aw-brown);
        border-color: var(--aw-brown);
        color: var(--aw-cream);
    }
    .aw-mobile-toggle:hover, .aw-sidebar-close:hover {
        background: #5C402F;
        color: #FFFFFF;
    }
    .aw-sidebar-menu:focus-visible,
    .aw-logout-button:focus-visible,
    .aw-mobile-toggle:focus-visible,
    .aw-sidebar-close:focus-visible {
        outline-color: var(--aw-accent);
    }
    /* keep the red badge readable on the brown active item */
    .aw-sidebar-menu.active .aw-menu-badge { box-shadow: 0 0 0 2px var(--aw-brown); }

    @media (max-width: 768px) {
        .aw-mobile-bar {
            background: var(--aw-cream);
            border-bottom-color: var(--aw-border);
        }
        .aw-mobile-bar-title { color: var(--aw-brown); }
        .aw-mobile-overlay { background: rgba(36, 25, 18, .44); }
    }

    /* Sidebar scrolling: brand/footer stay visible, menu scrolls independently */
    .aw-sidebar { min-height: 0; max-height: 100dvh; }
    .aw-sidebar-brand, .aw-sidebar-footer { flex: 0 0 auto; }
    .aw-sidebar-navigation {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto !important;
        overflow-x: hidden;
        overscroll-behavior-y: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #B9957B #F0E7DC;
        padding-bottom: 24px;
    }
    .aw-sidebar-navigation::-webkit-scrollbar { width: 7px; }
    .aw-sidebar-navigation::-webkit-scrollbar-track { background: #F0E7DC; border-radius: 12px; }
    .aw-sidebar-navigation::-webkit-scrollbar-thumb { background: #B9957B; border-radius: 12px; }
    .aw-sidebar-navigation::-webkit-scrollbar-thumb:hover { background: #70513D; }
    @media (max-width: 768px) {
        .aw-sidebar { height: 100dvh; max-height: 100dvh; }
        .aw-sidebar-navigation { touch-action: pan-y; }
    }
</style>

<aside class="aw-sidebar" id="awSidebar" aria-label="Admin navigation">

    <div class="aw-sidebar-brand">
        <button type="button" class="aw-sidebar-close" id="awSidebarClose" aria-label="Close navigation menu">
            <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        <div class="aw-brand-icon">AW</div>

        <div class="aw-brand-text">
            <h2 class="aw-brand-name">ALWAY WIN</h2>
            <div class="aw-brand-subtitle">Store Administration</div>
        </div>
    </div>

    <nav class="aw-sidebar-navigation">
        <p class="aw-nav-heading">MAIN MENU</p>

        @foreach ($sidebarMenus as $menu)
            @if (\Illuminate\Support\Facades\Route::has($menu['route']))
                <a
                    href="{{ route($menu['route']) }}"
                    class="aw-sidebar-menu {{ request()->routeIs($menu['active']) ? 'active' : '' }}"
                    title="{{ $menu['label'] }}"
                    aria-label="{{ $menu['label'] }}"
                    @if(request()->routeIs($menu['active'])) aria-current="page" @endif
                >
                    <span class="aw-menu-icon">
                        @switch($menu['icon'])
                            @case('layout-dashboard')
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="9" rx="1"/>
                                    <rect x="14" y="3" width="7" height="5" rx="1"/>
                                    <rect x="14" y="12" width="7" height="9" rx="1"/>
                                    <rect x="3" y="16" width="7" height="5" rx="1"/>
                                </svg>
                                @break

                            @case('package')
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 2 3 7l9 5 9-5-9-5Z"/>
                                    <path d="M3 7v10l9 5 9-5V7"/>
                                    <path d="M12 12v10"/>
                                </svg>
                                @break

                            @case('grid-2x2')
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                                @break

                            @case('warehouse')
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 21V8l9-5 9 5v13"/>
                                    <path d="M3 11h18M8 21v-6h8v6"/>
                                </svg>
                                @break

                          

                            @case('clipboard-list')
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="4" width="14" height="18" rx="2"/>
                                    <path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/>
                                </svg>
                                @break

                            @case('shopping-cart')
                                <svg viewBox="0 0 24 24">
                                    <circle cx="9" cy="20" r="1"/>
                                    <circle cx="19" cy="20" r="1"/>
                                    <path d="M2 3h2l3 13h13l2-9H5"/>
                                </svg>
                                @break

                                
                            @case('clipboard-list')
                                  <svg viewBox="0 0 24 24">
                                      <rect x="5" y="4" width="14" height="18" rx="2"/>
                                     <path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/>
                                    </svg>
                                    @break
                                    @case('image')
                                    <svg viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="m21 15-5-5L5 21"/>
                                    </svg>
                                   @break


                            @case('chart-column')
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 21h18M6 18v-7h3v7M11 18V5h3v13M16 18v-9h3v9"/>
                                </svg>
                                @break

                            @case('bell')
                                <svg viewBox="0 0 24 24">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
                                    <path d="M10 20a2 2 0 0 0 4 0"/>
                                </svg>
                                @break

                            @case('message-circle')
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/>
                                </svg>
                                @break
                                
                            @case('layers')
                                <svg viewBox="0 0 24 24">
                                    <path d="m12 3 9 5-9 5-9-5 9-5Z"/>
                                    <path d="m3 12 9 5 9-5"/>
                                    <path d="m3 16 9 5 9-5"/>
                                </svg>
                                @break


                            @case('users')
                                <svg viewBox="0 0 24 24">
                                    <circle cx="9" cy="8" r="3"/>
                                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                                    <path d="M17 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"/>
                                </svg>
                                @break
                        @endswitch
                    </span>

                    <span class="aw-menu-label">{{ $menu['label'] }}</span>

                    @if (!empty($menu['badge']))
                        <span class="aw-menu-badge">{{ $menu['badge'] > 99 ? '99+' : $menu['badge'] }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="aw-sidebar-footer">
        <div class="aw-sidebar-profile" title="{{ auth()->user()->name ?? 'Administrator' }}">
            <div class="aw-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>

            <div class="aw-profile-info">
                <p class="aw-profile-name">{{ auth()->user()->name ?? 'Administrator' }}</p>
                <span class="aw-profile-email">{{ auth()->user()->email ?? '' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf

            <button type="submit" class="aw-logout-button" title="Logout" aria-label="Logout">
                <span class="aw-menu-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M10 17l5-5-5-5M15 12H3"/>
                        <path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
                    </svg>
                </span>
                <span class="aw-menu-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- Bar + toggle + overlay must stay OUTSIDE the <aside>: a transformed parent breaks position:fixed children --}}
<div class="aw-mobile-bar">
    <button
        type="button"
        class="aw-mobile-toggle"
        id="awMobileToggle"
        aria-label="Open navigation menu"
        aria-controls="awSidebar"
        aria-expanded="false"
    >
        {{-- sidebar-panel icon --}}
        <svg viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="16" rx="3"/>
            <path d="M9 4v16"/>
        </svg>
    </button>
    <span class="aw-mobile-bar-title">ALWAY WIN</span>
</div>

<div class="aw-mobile-overlay" id="awMobileOverlay" aria-hidden="true"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar  = document.getElementById('awSidebar');
    const toggle   = document.getElementById('awMobileToggle');
    const overlay  = document.getElementById('awMobileOverlay');
    const closeBtn = document.getElementById('awSidebarClose');

    if (!sidebar || !toggle || !overlay) return;

    const isMobile = () => window.innerWidth <= 768;

    function setSidebarOpen(open) {
        document.body.classList.toggle('aw-sidebar-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');

        if (!isMobile()) return;
        if (open && closeBtn) closeBtn.focus({ preventScroll: true });
        if (!open) toggle.focus({ preventScroll: true });
    }

    toggle.addEventListener('click', function () {
        setSidebarOpen(!document.body.classList.contains('aw-sidebar-open'));
    });

    overlay.addEventListener('click', function () { setSidebarOpen(false); });
    if (closeBtn) closeBtn.addEventListener('click', function () { setSidebarOpen(false); });

    // close the drawer after tapping a menu link
    sidebar.querySelectorAll('.aw-sidebar-menu').forEach(function (link) {
        link.addEventListener('click', function () {
            document.body.classList.remove('aw-sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.body.classList.contains('aw-sidebar-open')) {
            setSidebarOpen(false);
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobile() && document.body.classList.contains('aw-sidebar-open')) {
            document.body.classList.remove('aw-sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>