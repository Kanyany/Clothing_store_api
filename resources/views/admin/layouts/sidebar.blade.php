<aside class="admin-sidebar">

    {{-- =====================================================
         PROFILE
    ====================================================== --}}

    <div class="sidebar-profile">

        <div class="profile-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>

        <div class="profile-info">

            <h3>
                {{ auth()->user()->name ?? 'Administrator' }}
            </h3>

            <p>
                {{ auth()->user()->email ?? 'admin@example.com' }}
            </p>

        </div>

    </div>


    {{-- =====================================================
         NAVIGATION
    ====================================================== --}}

    <nav class="sidebar-navigation">

        {{-- =================================================
             OVERVIEW
        ================================================== --}}

        <a
            href="{{ route('admin.dashboard') }}"
            class="sidebar-menu {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >

            <span class="menu-icon">
                ⌂
            </span>

            <span class="menu-label">
                Overview
            </span>

        </a>


        {{-- =================================================
             PRODUCTS
        ================================================== --}}

        <a
            href="{{ route('admin.products.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
        >

            <span class="menu-icon">
                ▣
            </span>

            <span class="menu-label">
                Products
            </span>

        </a>


        {{-- =================================================
             CATEGORIES
        ================================================== --}}

        <a
            href="{{ route('admin.categories.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
        >

            <span class="menu-icon">
                ◇
            </span>

            <span class="menu-label">
                Categories
            </span>

        </a>

        {{-- =================================================
            INVENTORY
        ================================================== --}}

        <a
            href="{{ route('admin.inventory.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
        >

            <span class="menu-icon">
                ▤
            </span>

            <span class="menu-label">
                Inventory
            </span>

        </a>


        {{-- =================================================
             PURCHASES
        ================================================== --}}

        <a
            href="{{ route('admin.purchases.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}"
        >

            <span class="menu-icon">
                □
            </span>

            <span class="menu-label">
                Purchases
            </span>

        </a>


        {{-- =================================================
             SALES
        ================================================== --}}

        <a
            href="{{ route('admin.transactions.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
        >
            <span class="menu-icon">◉</span>
            <span class="menu-label">Transactions</span>
        </a>



        {{-- =================================================
             REPORTS
        ================================================== --}}

       <a
            href="{{ route('admin.reports.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
        >
            <span class="menu-icon">▥</span>
            <span class="menu-label">Reports</span>
        </a>


        {{-- =================================================
             USERS
        ================================================== --}}

       <a
            href="{{ route('admin.users.index') }}"
            class="sidebar-menu {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
        >
            <span class="menu-icon">♙</span>
            <span class="menu-label">Users & Roles</span>
        </a>


        

    </nav>


    {{-- =====================================================
         LOGOUT
    ====================================================== --}}

    <div class="sidebar-footer">

        <form
            method="POST"
            action="{{ route('admin.logout') }}"
        >

            @csrf

            <button
                type="submit"
                class="logout-button"
            >

                <span class="menu-icon">
                    ↪
                </span>

                <span class="menu-label">
                    Logout
                </span>

            </button>

        </form>

    </div>

</aside>