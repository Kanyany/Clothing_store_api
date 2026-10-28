
@extends('admin.layouts.app')

@section('title', 'Customer Messages')

@section('content')

<style>
    .chat-page {
        --brown: #70513D;
        --cream: #FAF8F5;
        --line: #E9E0D7;
        --ink: #242326;
        display: grid;
        grid-template-columns: 330px minmax(0, 1fr);
        height: min(750px, calc(100dvh - 130px));
        min-height: 480px;
        max-width: 1150px;
        margin: 24px auto;
        background: white;
        border: 1px solid var(--line);
        border-radius: 18px;
        overflow: hidden;
        color: var(--ink);
        font-family: Inter, Arial, sans-serif;
    }

    .chat-page * { box-sizing: border-box; }

    .chat-left {
        display: flex;
        flex-direction: column;
        min-height: 0;
        border-right: 1px solid var(--line);
    }

    .chat-left-header {
        padding: 20px;
        border-bottom: 1px solid var(--line);
    }

    .chat-left-header h2 {
        margin: 0 0 14px;
        font-size: 22px;
        font-weight: 800;
    }

    .chat-search {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--cream);
        outline: none;
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }

    .chat-customer {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        margin-bottom: 5px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: white;
        text-align: left;
        cursor: pointer;
        font: inherit;
    }

    .chat-customer:hover,
    .chat-customer.active {
        background: #F5EEE6;
    }

    .chat-customer.waiting {
        border-color: #E6A9A3;
    }

    .chat-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--brown);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .chat-customer-info {
        flex: 1;
        min-width: 0;
    }

    .chat-customer-name {
        display: block;
        font-weight: 700;
        font-size: 14px;
    }

    .chat-customer-sub {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #8A8279;
    }

    .chat-right {
        display: flex;
        flex-direction: column;
        min-height: 0;
        background: var(--cream);
    }

    .chat-placeholder {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8A8279;
        text-align: center;
        padding: 24px;
    }

    .chat-panel {
        display: none;
        flex: 1;
        flex-direction: column;
        min-height: 0;
    }

    .chat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: white;
        border-bottom: 1px solid var(--line);
    }

    .chat-header-info {
        flex: 1;
        min-width: 0;
    }

    .chat-header-name {
        font-weight: 700;
    }

    .chat-status {
        font-size: 12px;
        color: #8A8279;
        margin-top: 3px;
    }

    .chat-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 14px;
        background: white;
        border-bottom: 1px solid var(--line);
    }

    .chat-btn {
        padding: 9px 13px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: white;
        color: var(--brown);
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .chat-btn.primary {
        background: var(--brown);
        color: white;
        border-color: var(--brown);
    }

    .chat-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .chat-stream {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 20px;
    }

    .chat-message {
        display: flex;
        flex-direction: column;
        max-width: 78%;
        align-self: flex-start;
    }

    .chat-message.admin {
        align-self: flex-end;
        align-items: flex-end;
    }

    .chat-bubble {
        padding: 12px 15px;
        background: white;
        border: 1px solid var(--line);
        border-radius: 16px 16px 16px 4px;
        font-size: 14px;
        line-height: 1.5;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .chat-message.admin .chat-bubble {
        background: var(--brown);
        color: white;
        border-color: var(--brown);
        border-radius: 16px 16px 4px 16px;
    }

    .chat-meta {
        margin-top: 5px;
        color: #8A8279;
        font-size: 11px;
    }

    .chat-compose {
        display: flex;
        gap: 10px;
        padding: 14px;
        background: white;
        border-top: 1px solid var(--line);
    }

    .chat-input {
        flex: 1;
        min-width: 0;
        padding: 12px 15px;
        border: 1px solid var(--line);
        border-radius: 24px;
        background: var(--cream);
        outline: none;
        font: inherit;
    }

    .chat-send {
        border: 0;
        border-radius: 24px;
        background: var(--brown);
        color: white;
        padding: 0 20px;
        font-weight: 700;
        cursor: pointer;
    }

    .chat-send:disabled,
    .chat-input:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .chat-error {
        display: none;
        padding: 10px 14px;
        color: #B42318;
        background: #FFF0EE;
        font-size: 13px;
    }

    .back-notifications-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 14px;
        padding: 9px 13px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
        color: var(--brown);
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .back-notifications-btn:hover {
        background: var(--brown);
        color: #fff;
        border-color: var(--brown);
    }

    .back-notifications-btn .back-arrow {
        font-size: 17px;
        line-height: 1;
    }

    .chat-back { display: none; }

    @media (max-width: 800px) {
        .chat-page {
            grid-template-columns: 1fr;
            height: calc(100dvh - 100px);
            margin: 0;
            border-radius: 0;
        }

        .chat-right { display: none; }

        .chat-page.mobile-open .chat-left {
            display: none;
        }

        .chat-page.mobile-open .chat-right {
            display: flex;
        }

        .chat-back { display: inline-block; }

        .chat-message { max-width: 88%; }
    }
</style>

<div
    class="chat-page"
    id="chatPage"
    data-list-url="{{ route('admin.chats.index') }}"
    data-chat-base="{{ url('/admin/chats') }}"
    data-conversation="{{ request('conversation') }}"
    data-admin-id="{{ auth()->id() }}"
>
    <aside class="chat-left">
        <div class="chat-left-header">
            <a
                href="{{ route('admin.notifications.index') }}"
                class="back-notifications-btn"
                aria-label="Back to Notifications"
            >
                <span class="back-arrow" aria-hidden="true">&larr;</span>
                Back to Notifications
            </a>

            <h2>Customer Messages</h2>

            <input
                type="search"
                id="chatSearch"
                class="chat-search"
                placeholder="Search customers..."
            >
        </div>

        <div class="chat-list" id="chatList">
            <p style="padding:14px;color:#8A8279">
                Loading conversations...
            </p>
        </div>
    </aside>

    <main class="chat-right">
        <div class="chat-placeholder" id="chatPlaceholder">
            Select a customer to view messages.
        </div>

        <div class="chat-panel" id="chatPanel">
            <header class="chat-header">
                <button
                    type="button"
                    class="chat-btn chat-back"
                    id="chatBack"
                >
                    ← Back
                </button>

                <div class="chat-avatar" id="chatHeaderAvatar">
                    C
                </div>

                <div class="chat-header-info">
                    <div class="chat-header-name" id="chatHeaderName">
                        Customer
                    </div>

                    <div class="chat-status" id="chatStatus"></div>
                </div>
            </header>

            <div class="chat-actions">
                <button
                    type="button"
                    class="chat-btn primary"
                    id="chatTakeOver"
                >
                    Take Over
                </button>

                <button
                    type="button"
                    class="chat-btn"
                    id="chatReturnBot"
                >
                    Return to Bot
                </button>

                <button
                    type="button"
                    class="chat-btn"
                    id="chatClose"
                >
                    Close Chat
                </button>

                <button
                    type="button"
                    class="chat-btn"
                    id="chatRefresh"
                >
                    Refresh
                </button>
            </div>

            <div class="chat-error" id="chatError"></div>

            <div class="chat-stream" id="chatStream"></div>

            <form class="chat-compose" id="chatForm">
                @csrf

                <input
                    type="text"
                    class="chat-input"
                    id="chatInput"
                    placeholder="Take over to reply..."
                    maxlength="2000"
                    disabled
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="chat-send"
                    id="chatSend"
                    disabled
                >
                    Send
                </button>
            </form>
        </div>
    </main>
</div>

<script>
(function () {
    'use strict';

    const root = document.getElementById('chatPage');

    if (!root) return;

    const listEl = document.getElementById('chatList');
    const searchEl = document.getElementById('chatSearch');
    const panel = document.getElementById('chatPanel');
    const placeholder = document.getElementById('chatPlaceholder');
    const stream = document.getElementById('chatStream');
    const input = document.getElementById('chatInput');
    const sendButton = document.getElementById('chatSend');
    const errorEl = document.getElementById('chatError');

    const takeOverButton = document.getElementById('chatTakeOver');
    const returnBotButton = document.getElementById('chatReturnBot');
    const closeButton = document.getElementById('chatClose');

    const listUrl = root.dataset.listUrl;
    const baseUrl = root.dataset.chatBase;
    const adminId = Number(root.dataset.adminId);
    const csrf = @json(csrf_token());

    let conversations = [];
    let current = null;
    let sending = false;

    function showError(message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function clearError() {
        errorEl.textContent = '';
        errorEl.style.display = 'none';
    }

    async function api(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                ...(options.headers || {})
            }
        });

        let result;

        try {
            result = await response.json();
        } catch (error) {
            throw new Error(
                'Server returned an invalid response. Check your login.'
            );
        }

        if (!response.ok || result.success === false) {
            const validationError = result.errors
                ? Object.values(result.errors).flat()[0]
                : null;

            throw new Error(
                validationError ||
                result.message ||
                'Request failed (' + response.status + ')'
            );
        }

        return result;
    }

    function chatUrl(id, action = '') {
        return baseUrl + '/' +
            encodeURIComponent(id) +
            (action ? '/' + action : '');
    }

    function formatTime(value) {
        if (!value) return '';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) return '';

        return date.toLocaleString();
    }

    function customerName(conversation) {
        return conversation.customer?.name || 'Customer';
    }

    function renderList() {
        listEl.replaceChildren();

        const query = searchEl.value.trim().toLowerCase();

        const filtered = conversations.filter(conversation =>
            customerName(conversation)
                .toLowerCase()
                .includes(query)
        );

        if (!filtered.length) {
            const empty = document.createElement('p');
            empty.style.padding = '14px';
            empty.style.color = '#8A8279';
            empty.textContent = 'No conversations found.';
            listEl.appendChild(empty);
            return;
        }

        filtered.forEach(conversation => {
            const button = document.createElement('button');

            button.type = 'button';

            button.className = 'chat-customer';

            if (current &&
                Number(current.id) === Number(conversation.id)) {
                button.classList.add('active');
            }

            if (conversation.mode === 'waiting_admin') {
                button.classList.add('waiting');
            }

            const avatar = document.createElement('span');
            avatar.className = 'chat-avatar';
            avatar.textContent = customerName(conversation)
                .charAt(0)
                .toUpperCase();

            const info = document.createElement('span');
            info.className = 'chat-customer-info';

            const name = document.createElement('span');
            name.className = 'chat-customer-name';
            name.textContent = customerName(conversation);

            const sub = document.createElement('span');
            sub.className = 'chat-customer-sub';

            sub.textContent =
                conversation.mode.replace('_', ' ') +
                ' · ' +
                (conversation.messages_count || 0) +
                ' messages';

            info.append(name, sub);
            button.append(avatar, info);

            button.addEventListener('click', () => {
                openChat(conversation.id);
            });

            listEl.appendChild(button);
        });
    }

    async function loadConversations() {
        const result = await api(listUrl);

        conversations = result.conversations?.data || [];

        renderList();
    }

    function renderMessage(message) {
        const wrap = document.createElement('div');

        wrap.className = 'chat-message';

        if (message.sender_type === 'admin') {
            wrap.classList.add('admin');
        }

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble';
        bubble.textContent = message.message || '';

        const meta = document.createElement('div');
        meta.className = 'chat-meta';

        meta.textContent =
            (message.sender_type || 'customer') +
            ' · ' +
            formatTime(message.created_at);

        wrap.append(bubble, meta);

        stream.appendChild(wrap);
    }

    function updateControls() {
        if (!current) return;

        const open = current.status === 'open';

        const owned =
            open &&
            current.mode === 'admin' &&
            Number(current.assigned_admin_id) === adminId;

        const assignedToOther =
            current.assigned_admin_id !== null &&
            Number(current.assigned_admin_id) !== adminId;

        takeOverButton.disabled =
            !open || assignedToOther || owned || sending;

        returnBotButton.disabled = !owned || sending;

        closeButton.disabled =
            !open || assignedToOther || sending;

        input.disabled = !owned || sending;
        sendButton.disabled = !owned || sending;

        input.placeholder = owned
            ? 'Type a message...'
            : 'Take over to reply...';

        document.getElementById('chatStatus').textContent =
            'Mode: ' + current.mode +
            ' · Status: ' + current.status;
    }

    async function openChat(id) {
        clearError();

        try {
            const result = await api(chatUrl(id));

            current = result.conversation;

            const name = customerName(current);

            document.getElementById('chatHeaderName')
                .textContent = name;

            document.getElementById('chatHeaderAvatar')
                .textContent = name.charAt(0).toUpperCase();

            stream.replaceChildren();

            const messages = result.messages?.data || [];

            messages.forEach(renderMessage);

            placeholder.style.display = 'none';
            panel.style.display = 'flex';

            root.classList.add('mobile-open');

            updateControls();
            renderList();

            stream.scrollTop = stream.scrollHeight;

            const url = new URL(window.location.href);
            url.searchParams.set('conversation', current.id);

            history.replaceState(null, '', url);

        } catch (error) {
            showError(error.message);
            alert(error.message);
        }
    }

    async function performAction(action) {
        if (!current) return;

        clearError();

        const id = current.id;

        try {
            await api(chatUrl(id, action), {
                method: 'POST',
                body: JSON.stringify({})
            });

            await openChat(id);
            await loadConversations();

        } catch (error) {
            showError(error.message);
        }
    }

    takeOverButton.addEventListener('click', () => {
        performAction('take-over');
    });

    returnBotButton.addEventListener('click', () => {
        performAction('return-to-bot');
    });

    closeButton.addEventListener('click', () => {
        if (confirm('Close this conversation?')) {
            performAction('close');
        }
    });

    document.getElementById('chatRefresh')
        .addEventListener('click', async () => {
            if (!current) return;

            await openChat(current.id);
            await loadConversations();
        });

    document.getElementById('chatBack')
        .addEventListener('click', () => {
            root.classList.remove('mobile-open');
        });

    searchEl.addEventListener('input', renderList);

    document.getElementById('chatForm')
        .addEventListener('submit', async event => {
            event.preventDefault();

            if (!current || sending || input.disabled) return;

            const text = input.value.trim();

            if (!text) return;

            const id = current.id;

            sending = true;
            clearError();
            updateControls();

            try {
                // Save to real chat_messages table through
                // the existing AdminChatController.
                await api(chatUrl(id, 'reply'), {
                    method: 'POST',
                    body: JSON.stringify({
                        message: text
                    })
                });

                // Only clear the input after successful save.
                input.value = '';

                await openChat(id);
                await loadConversations();

            } catch (error) {
                showError(error.message);

            } finally {
                sending = false;
                updateControls();
            }
        });

    async function initialize() {
        try {
            await loadConversations();

            const requestedId = root.dataset.conversation;

            if (requestedId) {
                await openChat(requestedId);
            } else if (
                conversations.length &&
                window.innerWidth > 800
            ) {
                await openChat(conversations[0].id);
            }

        } catch (error) {
            listEl.textContent = error.message;
        }
    }

    initialize();
})();
</script>

@endsection
