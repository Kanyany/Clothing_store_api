<header class="admin-navbar">

    {{-- Brand --}}
    <div class="navbar-brand">

        <div class="brand-mark">
            CP
        </div>

        <div class="brand-text">
            <span class="brand-name">
                Clothing POS
            </span>

            <span class="brand-subtitle">
                Admin Panel
            </span>
        </div>

    </div>


    {{-- Page Information --}}
    <div class="navbar-page">

        <span class="page-label">
            @yield('page-title', 'Overview')
        </span>

    </div>


    {{-- Navbar Actions --}}
    <div class="navbar-actions">

        {{-- GLOBAL SEARCH --}}
        <div class="navbar-search" id="globalSearch">

            <span class="search-icon">
                ⌕
            </span>

            <input
                id="globalSearchInput"
                type="text"
                placeholder="Search..."
                aria-label="Search"
                autocomplete="off"
            >

            <div
                id="globalSearchResults"
                class="global-search-results"
            ></div>

        </div>


        {{-- Notification --}}
        <button
            type="button"
            class="navbar-icon-button"
            aria-label="Notifications"
        >
            ♢

            <span class="notification-dot"></span>
        </button>


        {{-- Admin Profile --}}
        <div class="navbar-user">

            <div class="navbar-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>

            <div class="navbar-user-info">

                <span class="navbar-user-name">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </span>

                <span class="navbar-user-role">
                    Admin
                </span>

            </div>

        </div>

    </div>

</header>


<style>

    /* =========================================================
       GLOBAL SEARCH
    ========================================================== */

    .navbar-search {
        position: relative;
    }

    .global-search-results {
        position: absolute;

        top: calc(100% + 10px);
        right: 0;

        width: 360px;
        max-height: 430px;

        overflow-y: auto;

        background: #FFFFFF;

        border: 1px solid rgba(44, 30, 23, 0.08);
        border-radius: 14px;

        box-shadow:
            0 15px 40px rgba(44, 30, 23, 0.12);

        display: none;

        z-index: 9999;
    }

    .global-search-results.show {
        display: block;
    }


    /* SEARCH GROUP */

    .search-result-group {
        padding: 8px 0;
    }

    .search-result-group + .search-result-group {
        border-top: 1px solid rgba(44, 30, 23, 0.06);
    }


    .search-result-group-title {
        padding: 7px 14px;

        color: #8B8580;

        font-size: 10px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: 0.7px;
    }


    /* RESULT ITEM */

    .search-result-item {
        display: flex;

        align-items: center;

        gap: 10px;

        width: 100%;

        padding: 10px 14px;

        color: #24201E;

        text-decoration: none;

        transition: background 0.15s ease;
    }

    .search-result-item:hover {
        background: #F5EDE3;
    }


    .search-result-icon {
        width: 30px;
        height: 30px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 9px;

        background: #F5EDE3;

        color: #6F4E37;

        font-size: 13px;
        font-weight: 700;
    }


    .search-result-content {
        min-width: 0;

        display: flex;
        flex-direction: column;

        gap: 2px;
    }


    .search-result-title {
        color: #2C1E17;

        font-size: 12px;
        font-weight: 700;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    .search-result-subtitle {
        color: #8B8580;

        font-size: 10px;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    /* EMPTY */

    .search-empty {
        padding: 24px 16px;

        text-align: center;

        color: #8B8580;

        font-size: 11px;
    }


    /* LOADING */

    .search-loading {
        padding: 18px;

        text-align: center;

        color: #8B8580;

        font-size: 11px;
    }


    /* RESPONSIVE */

    @media (max-width: 600px) {

        .global-search-results {
            position: fixed;

            top: 70px;
            left: 12px;
            right: 12px;

            width: auto;
        }

    }

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('globalSearchInput');
    const resultsBox = document.getElementById('globalSearchResults');
    const searchContainer = document.getElementById('globalSearch');

    if (!input || !resultsBox) {
        return;
    }


    let timer = null;


    /*
    |--------------------------------------------------------------------------
    | ICON
    |--------------------------------------------------------------------------
    */

    function getIcon(type) {

        switch (type) {

            case 'Products':
                return '👕';

            case 'Categories':
                return '◇';

            case 'Transactions':
                return '◉';

            case 'Purchases':
                return '□';

            case 'Users':
                return '♙';

            default:
                return '•';
        }

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW RESULTS
    |--------------------------------------------------------------------------
    */

    function renderResults(results) {

        if (!results.length) {

            resultsBox.innerHTML = `
                <div class="search-empty">
                    No results found
                </div>
            `;

            resultsBox.classList.add('show');

            return;
        }


        const groups = {};


        results.forEach(function (result) {

            if (!groups[result.type]) {
                groups[result.type] = [];
            }

            groups[result.type].push(result);

        });


        let html = '';


        Object.keys(groups).forEach(function (type) {

            html += `
                <div class="search-result-group">

                    <div class="search-result-group-title">
                        ${escapeHtml(type)}
                    </div>
            `;


            groups[type].forEach(function (result) {

                html += `
                    <a
                        href="${escapeHtml(result.url)}"
                        class="search-result-item"
                    >

                        <div class="search-result-icon">
                            ${getIcon(result.type)}
                        </div>

                        <div class="search-result-content">

                            <span class="search-result-title">
                                ${escapeHtml(result.title)}
                            </span>

                            <span class="search-result-subtitle">
                                ${escapeHtml(result.subtitle)}
                            </span>

                        </div>

                    </a>
                `;

            });


            html += `
                </div>
            `;

        });


        resultsBox.innerHTML = html;

        resultsBox.classList.add('show');

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH DATABASE
    |--------------------------------------------------------------------------
    */

    async function searchDatabase(term) {

        if (!term.trim()) {

            resultsBox.innerHTML = '';
            resultsBox.classList.remove('show');

            return;
        }


        resultsBox.innerHTML = `
            <div class="search-loading">
                Searching...
            </div>
        `;

        resultsBox.classList.add('show');


        try {

            const url =
                `{{ route('admin.global-search') }}?q=`
                + encodeURIComponent(term);


            const response = await fetch(url, {

                method: 'GET',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }

            });


            if (!response.ok) {
                throw new Error('Search request failed.');
            }


            const data = await response.json();


            renderResults(data.results || []);

        } catch (error) {

            console.error(error);

            resultsBox.innerHTML = `
                <div class="search-empty">
                    Unable to search right now.
                </div>
            `;

            resultsBox.classList.add('show');

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    input.addEventListener('input', function () {

        const term = this.value;


        clearTimeout(timer);


        timer = setTimeout(function () {

            searchDatabase(term);

        }, 250);

    });


    /*
    |--------------------------------------------------------------------------
    | ENTER
    |--------------------------------------------------------------------------
    */

    input.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            input.value = '';

            resultsBox.innerHTML = '';

            resultsBox.classList.remove('show');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | CLICK OUTSIDE
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        if (
            searchContainer &&
            !searchContainer.contains(event.target)
        ) {

            resultsBox.classList.remove('show');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | FOCUS
    |--------------------------------------------------------------------------
    */

    input.addEventListener('focus', function () {

        if (input.value.trim() !== '') {
            resultsBox.classList.add('show');
        }

    });

});

</script>