
@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')

@php
    // Notifications come from NotificationController.
    // No sample data is used.
    $notifications = collect($notifications ?? []);

    $normalizeType = function ($notification) {
        $type = $notification['type'] ?? 'system';
        $icon = $notification['icon'] ?? '';

        if ($type === 'stock' || $icon === 'stock') {
            return 'stock';
        }

        if (in_array($type, ['order', 'orders'])) {
            return 'order';
        }

        if (in_array($type, ['message', 'messages'])) {
            return 'message';
        }

        return 'system';
    };

    $notifications = $notifications->map(function ($n) use ($normalizeType) {
        $n['type'] = $normalizeType($n);

        $n['icon'] = $n['icon'] ?? $n['type'];
        $n['title'] = $n['title'] ?? 'Notification';
        $n['text'] = $n['text'] ?? '';
        $n['time'] = $n['time'] ?? '';
        $n['unread'] = (bool) ($n['unread'] ?? false);
        $n['url'] = $n['url'] ?? '#';

        return $n;
    });

    $count = fn ($type) => $notifications
        ->where('type', $type)
        ->where('unread', true)
        ->count();

    $unreadAll = $notifications
        ->where('unread', true)
        ->count();

    $readAllRoute = route('admin.notifications.read-all');
    $readBaseUrl = url('/admin/notifications');
@endphp

<style>
    .awn-page {
        --awn-brown: #70513D;
        --awn-brown-dark: #5A3F2C;
        --awn-cream: #FAF8F5;
        --awn-line: #E9E0D7;
        --awn-ink: #242326;
        --awn-muted: #8A8279;
        --awn-red: #D9534F;

        max-width: 900px;
        margin: 0 auto;
        padding: 28px 20px 40px;
        font-family: Inter, Arial, sans-serif;
        color: var(--awn-ink);
    }

    .awn-page *,
    .awn-page *::before,
    .awn-page *::after {
        box-sizing: border-box;
    }

    .awn-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .awn-eyebrow {
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.6px;
        color: var(--awn-brown);
    }

    .awn-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .awn-sub {
        margin: 6px 0 0;
        font-size: 13px;
        color: var(--awn-muted);
    }

    .awn-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 16px;
        border: 1px solid var(--awn-line);
        border-radius: 12px;
        background: white;
        color: var(--awn-brown);
        font: inherit;
        font-size: 13px;
        font-weight: 650;
        cursor: pointer;
        transition: background .15s ease;
    }

    .awn-btn:hover {
        background: #F5EFE8;
    }

    .awn-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    /* Filter tabs */

    .awn-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        margin-bottom: 16px;
        scrollbar-width: none;
    }

    .awn-tabs::-webkit-scrollbar {
        display: none;
    }

    .awn-tab {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 38px;
        padding: 0 16px;
        border: 1px solid var(--awn-line);
        border-radius: 999px;
        background: white;
        color: #6B655D;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    .awn-tab:hover {
        background: #F5EFE8;
    }

    .awn-tab.active {
        background: var(--awn-brown);
        border-color: var(--awn-brown);
        color: white;
    }

    .awn-tab-count {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: var(--awn-red);
        color: white;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .awn-tab-count[data-zero="1"] {
        display: none;
    }

    /* Notification list */

    .awn-list {
        background: white;
        border: 1px solid var(--awn-line);
        border-radius: 18px;
        overflow: hidden;
    }

    .awn-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid #F0EAE2;
        text-decoration: none;
        color: inherit;
        transition: background .15s ease;
    }

    .awn-item:last-of-type {
        border-bottom: 0;
    }

    .awn-item:hover {
        background: #FBF8F4;
        color: inherit;
    }

    .awn-item.unread {
        background: #FFFAF5;
    }

    .awn-item.is-hidden {
        display: none;
    }

    .awn-icon {
        position: relative;
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #F3ECE4;
        color: var(--awn-brown);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .awn-icon svg {
        width: 21px;
        height: 21px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .awn-dot {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--awn-red);
        border: 2px solid white;
    }

    .awn-item:not(.unread) .awn-dot {
        display: none;
    }

    .awn-body {
        flex: 1;
        min-width: 0;
    }

    .awn-item-title {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
    }

    .awn-item-text {
        margin: 3px 0 0;
        font-size: 13px;
        color: var(--awn-muted);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .awn-time {
        flex-shrink: 0;
        font-size: 12px;
        color: #A49D94;
    }

    /* Highlight customer messages */

    .awn-item.type-message.unread {
        margin: 6px 10px;
        border: 1.5px solid #E6A9A3;
        border-radius: 14px;
        background: white;
    }

    .awn-item.type-message.unread:hover {
        background: #FFFAF9;
    }

    .awn-reply {
        display: none;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 700;
        color: var(--awn-brown);
    }

    .awn-item.type-message .awn-reply {
        display: inline-block;
    }

    .awn-empty {
        display: none;
        padding: 46px 20px;
        text-align: center;
        color: var(--awn-muted);
        font-size: 14px;
    }

    .awn-empty.show {
        display: block;
    }

    .awn-error {
        display: none;
        margin-bottom: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        background: #FFF0EE;
        color: #B42318;
        font-size: 13px;
    }

    .awn-error.show {
        display: block;
    }

    @media (max-width: 768px) {
        .awn-page {
            padding: 18px 14px 32px;
        }

        .awn-title {
            font-size: 26px;
        }

        .awn-btn {
            width: 100%;
        }

        .awn-item {
            padding: 14px;
            gap: 12px;
        }

        .awn-icon {
            width: 42px;
            height: 42px;
        }

        .awn-item-text {
            white-space: normal;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .awn-time {
            align-self: flex-start;
            margin-top: 2px;
        }
    }
</style>

<div class="awn-page" id="awnPage"
     data-read-all-url="{{ $readAllRoute }}"
     data-read-base-url="{{ $readBaseUrl }}"
     data-poll-url="{{ route('admin.notifications.index', ['ajax' => 1]) }}">

    <div class="awn-head">
        <div>
            <p class="awn-eyebrow">ACTIVITY</p>

            <h1 class="awn-title">Notifications</h1>

            <p class="awn-sub">
                Orders, customer messages and system alerts in one place.
            </p>
        </div>

        <button type="button" class="awn-btn" id="awnMarkAll">
            <svg width="16" height="16"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M4 12l5 5L20 6"/>
            </svg>

            Mark all as read
        </button>
    </div>

    <div class="awn-error" id="awnError"></div>

    <div class="awn-tabs"
         role="tablist"
         aria-label="Filter notifications">

        <button type="button"
                class="awn-tab active"
                data-filter="all"
                role="tab"
                aria-selected="true">

            All

            <span class="awn-tab-count"
                  data-count="all"
                  data-zero="{{ $unreadAll ? '0' : '1' }}">
                {{ $unreadAll }}
            </span>
        </button>

        <button type="button"
                class="awn-tab"
                data-filter="order"
                role="tab"
                aria-selected="false">

            Orders

            <span class="awn-tab-count"
                  data-count="order"
                  data-zero="{{ $count('order') ? '0' : '1' }}">
                {{ $count('order') }}
            </span>
        </button>

        <button type="button"
                class="awn-tab"
                data-filter="message"
                role="tab"
                aria-selected="false">

            Messages

            <span class="awn-tab-count"
                  data-count="message"
                  data-zero="{{ $count('message') ? '0' : '1' }}">
                {{ $count('message') }}
            </span>
        </button>

        <button type="button"
                class="awn-tab"
                data-filter="stock"
                role="tab"
                aria-selected="false">

            Stock

            <span class="awn-tab-count"
                  data-count="stock"
                  data-zero="{{ $count('stock') ? '0' : '1' }}">
                {{ $count('stock') }}
            </span>
        </button>

    </div>

    <div class="awn-list" id="awnList">

        @foreach ($notifications as $n)

            @php
                $href = $n['url'];

                $notificationId = (string) ($n['id'] ?? '');

                $isWaitingChat = str_starts_with(
                    $notificationId,
                    'chat-'
                );
            @endphp

            <a href="{{ $href }}"
               class="awn-item type-{{ $n['type'] }} {{ $n['unread'] ? 'unread' : '' }}"
               data-type="{{ $n['type'] }}"
               data-id="{{ $notificationId }}"
               data-url="{{ $href }}"
               data-chat="{{ $isWaitingChat ? '1' : '0' }}">

                <span class="awn-icon">

                    @switch($n['icon'])

                        @case('order')
                            <svg viewBox="0 0 24 24">
                                <path d="M6 7h12l-1 13H7L6 7Z"/>
                                <path d="M9 7a3 3 0 0 1 6 0"/>
                            </svg>
                            @break

                        @case('message')
                            <svg viewBox="0 0 24 24">
                                <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/>
                                <path d="M8.5 12h.01M12 12h.01M15.5 12h.01"/>
                            </svg>
                            @break

                        @case('stock')
                            <svg viewBox="0 0 24 24">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
                                <path d="M10 20a2 2 0 0 0 4 0"/>
                            </svg>
                            @break

                        @case('promo')
                            <svg viewBox="0 0 24 24">
                                <path d="M3 12V4h8l10 10-8 8L3 12Z"/>
                                <circle cx="7.5" cy="8.5" r="1"/>
                            </svg>
                            @break

                        @default
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M8 12l3 3 5-6"/>
                            </svg>

                    @endswitch

                    <span class="awn-dot"></span>
                </span>

                <span class="awn-body">

                    <p class="awn-item-title">
                        {{ $n['title'] }}
                    </p>

                    <p class="awn-item-text">
                        {{ $n['text'] }}
                    </p>

                    @if ($n['type'] === 'message')
                        <span class="awn-reply">
                            Reply in Messages →
                        </span>
                    @endif
                </span>

                <span class="awn-time">
                    {{ $n['time'] }}
                </span>
            </a>

        @endforeach

        <div class="awn-empty" id="awnEmpty">
            No notifications here yet.
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const page = document.getElementById('awnPage');
    if (!page) return;

    const list = document.getElementById('awnList');
    const empty = document.getElementById('awnEmpty');
    const tabs = page.querySelectorAll('.awn-tab');
    const markAll = document.getElementById('awnMarkAll');
    const errorBox = document.getElementById('awnError');
    const csrf = @json(csrf_token());
    const readAllUrl = page.dataset.readAllUrl;
    const readBaseUrl = page.dataset.readBaseUrl;
    const pollUrl = page.dataset.pollUrl;
    const POLL_MS = 3000;

    let activeFilter = 'all';
    let polling = false;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.add('show');
    }

    function clearError() {
        errorBox.textContent = '';
        errorBox.classList.remove('show');
    }

    function setCount(type, count) {
        const el = page.querySelector('[data-count="' + type + '"]');
        if (!el) return;
        el.textContent = String(count);
        el.dataset.zero = count === 0 ? '1' : '0';
    }

    function refreshCounts() {
        let total = 0;
        ['order', 'message', 'stock'].forEach(type => {
            const count = list.querySelectorAll(
                '.awn-item.unread[data-type="' + type + '"]'
            ).length;
            setCount(type, count);
            total += count;
        });
        setCount('all', total);
    }

    function applyFilter(type) {
        activeFilter = type;
        let visible = 0;
        list.querySelectorAll('.awn-item').forEach(item => {
            const show = type === 'all' || item.dataset.type === type;
            item.classList.toggle('is-hidden', !show);
            if (show) visible++;
        });
        empty.classList.toggle('show', visible === 0);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(other => {
                other.classList.remove('active');
                other.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            applyFilter(tab.dataset.filter);
        });
    });

    async function post(url) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json().catch(() => null);
        if (!response.ok || result?.success === false) {
            throw new Error(result?.message || 'Request failed (' + response.status + ')');
        }
        return result;
    }

    function makeIcon(type) {
        const wrap = document.createElement('span');
        wrap.className = 'awn-icon';
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');

        const addPath = d => {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            svg.appendChild(path);
        };

        if (type === 'message') {
            addPath('M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z');
            addPath('M8.5 12h.01M12 12h.01M15.5 12h.01');
        } else if (type === 'order') {
            addPath('M6 7h12l-1 13H7L6 7Z');
            addPath('M9 7a3 3 0 0 1 6 0');
        } else if (type === 'stock') {
            addPath('M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8');
            addPath('M10 20a2 2 0 0 0 4 0');
        } else {
            addPath('M12 3a9 9 0 1 0 0 18a9 9 0 1 0 0-18');
            addPath('M8 12l3 3 5-6');
        }

        wrap.appendChild(svg);
        const dot = document.createElement('span');
        dot.className = 'awn-dot';
        wrap.appendChild(dot);
        return wrap;
    }

    function makeItem(n) {
        const a = document.createElement('a');
        a.href = n.url || '#';
        a.className = 'awn-item type-' + (n.type || 'system') + (n.unread ? ' unread' : '');
        a.dataset.type = n.type || 'system';
        a.dataset.id = String(n.id ?? '');
        a.dataset.url = n.url || '#';
        a.dataset.chat = n.chat ? '1' : '0';
        if (n.product_variant_id !== null && n.product_variant_id !== undefined) {
            a.dataset.productVariantId = String(n.product_variant_id);
        }

        a.appendChild(makeIcon(n.type || 'system'));

        const body = document.createElement('span');
        body.className = 'awn-body';

        const title = document.createElement('p');
        title.className = 'awn-item-title';
        title.textContent = n.title || 'Notification';

        const message = document.createElement('p');
        message.className = 'awn-item-text';
        message.textContent = n.text || '';

        body.append(title, message);

        if (n.type === 'message') {
            const reply = document.createElement('span');
            reply.className = 'awn-reply';
            reply.textContent = 'Reply in Messages →';
            body.appendChild(reply);
        }

        const time = document.createElement('span');
        time.className = 'awn-time';
        time.textContent = n.time || '';

        a.append(body, time);
        return a;
    }

    function renderNotifications(notifications) {
        const oldItems = new Map();
        list.querySelectorAll('.awn-item').forEach(item => {
            oldItems.set(item.dataset.id, item);
        });

        const fragment = document.createDocumentFragment();
        notifications.forEach(n => {
            const key = String(n.id ?? '');
            const old = oldItems.get(key);

            // Keep the currently displayed read state for stored items if
            // the page has already marked them read in this session.
            if (old && !old.classList.contains('unread')) {
                n.unread = false;
            }

            const item = makeItem(n);
            fragment.appendChild(item);
        });

        list.querySelectorAll('.awn-item').forEach(item => item.remove());
        list.insertBefore(fragment, empty);
        refreshCounts();
        applyFilter(activeFilter);
    }

    list.addEventListener('click', async event => {
        const item = event.target.closest('.awn-item');
        if (!item || !list.contains(item)) return;

        const id = item.dataset.id;
        const href = item.dataset.url;
        const isChat = item.dataset.chat === '1';

        // Chat notification click opens the conversation; read state is
        // updated by the chat endpoint when the admin opens the conversation.
        if (isChat || !id || !item.classList.contains('unread')) return;

        event.preventDefault();
        clearError();
        try {
            await post(readBaseUrl + '/' + encodeURIComponent(id) + '/read');
            item.classList.remove('unread');
            refreshCounts();
            if (href && href !== '#') window.location.assign(href);
        } catch (error) {
            showError(error.message);
        }
    });

    markAll.addEventListener('click', async () => {
        markAll.disabled = true;
        clearError();
        try {
            await post(readAllUrl);
            list.querySelectorAll('.awn-item').forEach(item => {
                if (item.dataset.chat !== '1') item.classList.remove('unread');
            });
            refreshCounts();
        } catch (error) {
            showError(error.message);
        } finally {
            markAll.disabled = false;
        }
    });

    async function poll() {
        if (polling || document.hidden) return;
        polling = true;
        try {
            const response = await fetch(pollUrl, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });
            if (!response.ok) throw new Error('Could not check for new notifications.');
            const result = await response.json();
            if (result.success && Array.isArray(result.notifications)) {
                renderNotifications(result.notifications);
                clearError();
            }
        } catch (error) {
            // Keep existing notifications visible if a temporary poll fails.
            console.warn(error);
        } finally {
            polling = false;
        }
    }

    refreshCounts();
    applyFilter('all');
    window.setInterval(poll, POLL_MS);
})();
</script>

@endsection
