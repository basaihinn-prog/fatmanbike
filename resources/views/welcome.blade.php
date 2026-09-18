<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>{{ config('app.name', 'Casino') }}</title>
    <style>
        :root {
            --bg: #07090d;
            --panel: #10141b;
            --panel-2: #151a23;
            --text: #f7f8fb;
            --muted: #98a2b3;
            --line: rgba(255,255,255,.08);
            --accent: #8b5cf6;
            --accent-2: #22d3ee;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            background:
                radial-gradient(circle at 15% -10%, rgba(139,92,246,.20), transparent 34rem),
                radial-gradient(circle at 100% 0%, rgba(34,211,238,.10), transparent 30rem),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body { min-height: 100vh; }

        button, input { font: inherit; }

        .shell {
            width: min(1440px, calc(100% - 32px));
            margin: 0 auto;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid var(--line);
            background: rgba(7,9,13,.86);
            backdrop-filter: blur(18px);
        }

        .topbar {
            min-height: 72px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-weight: 800;
            letter-spacing: -.03em;
            white-space: nowrap;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 10px 28px rgba(139,92,246,.28);
        }

        .search {
            margin-left: auto;
            width: min(460px, 48vw);
            position: relative;
        }

        .search input {
            width: 100%;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.045);
            color: var(--text);
            outline: none;
            border-radius: 14px;
            padding: 12px 15px;
        }

        .search input:focus {
            border-color: rgba(139,92,246,.65);
            box-shadow: 0 0 0 3px rgba(139,92,246,.12);
        }

        main { padding: 34px 0 64px; }

        .hero {
            border: 1px solid var(--line);
            background:
                linear-gradient(120deg, rgba(139,92,246,.18), rgba(34,211,238,.06)),
                var(--panel);
            border-radius: 24px;
            padding: clamp(24px, 4vw, 44px);
            overflow: hidden;
            position: relative;
        }

        .hero:after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -80px;
            top: -80px;
            border-radius: 50%;
            background: rgba(139,92,246,.18);
            filter: blur(20px);
        }

        .hero h1 {
            margin: 0;
            max-width: 760px;
            font-size: clamp(30px, 5vw, 58px);
            line-height: 1.02;
            letter-spacing: -.055em;
        }

        .hero p {
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .section-head {
            margin: 30px 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .section-head h2 {
            margin: 0;
            font-size: 21px;
            letter-spacing: -.025em;
        }

        .status {
            color: var(--muted);
            font-size: 13px;
        }

        .providers {
            display: flex;
            gap: 9px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }

        .provider {
            flex: 0 0 auto;
            border: 1px solid var(--line);
            color: #d7dce4;
            background: var(--panel);
            border-radius: 999px;
            padding: 9px 14px;
            cursor: pointer;
        }

        .provider:hover,
        .provider.active {
            border-color: rgba(139,92,246,.55);
            background: rgba(139,92,246,.16);
            color: white;
        }

        .games {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
        }

        .card {
            appearance: none;
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 18px;
            overflow: hidden;
            color: inherit;
            text-align: left;
            cursor: pointer;
            padding: 0;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            border-color: rgba(139,92,246,.45);
            box-shadow: 0 18px 38px rgba(0,0,0,.28);
        }

        .thumb {
            aspect-ratio: 16 / 10;
            width: 100%;
            background:
                linear-gradient(135deg, rgba(139,92,246,.18), rgba(34,211,238,.08)),
                var(--panel-2);
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .thumb-fallback {
            font-size: 13px;
            color: var(--muted);
            padding: 16px;
            text-align: center;
        }

        .meta { padding: 13px 14px 15px; }

        .game-name {
            font-weight: 750;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .provider-name {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
            text-transform: capitalize;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .empty, .error {
            grid-column: 1 / -1;
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 18px;
            padding: 26px;
            color: var(--muted);
            text-align: center;
        }

        .error { color: #fca5a5; }

        .load-more-wrap {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .load-more {
            border: 1px solid var(--line);
            background: var(--panel-2);
            color: white;
            border-radius: 12px;
            padding: 11px 18px;
            cursor: pointer;
        }

        .load-more:disabled {
            opacity: .45;
            cursor: default;
        }

        .toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            pointer-events: none;
            background: #151923;
            color: white;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 14px;
            box-shadow: 0 16px 40px rgba(0,0,0,.38);
            transition: .2s ease;
            z-index: 100;
            max-width: min(90vw, 520px);
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        @media (max-width: 1100px) {
            .games { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        @media (max-width: 820px) {
            .games { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .topbar { flex-wrap: wrap; padding: 12px 0; }
            .search { order: 3; width: 100%; }
        }

        @media (max-width: 580px) {
            .shell { width: min(100% - 20px, 1440px); }
            main { padding-top: 18px; }
            .hero { border-radius: 18px; }
            .games { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .card { border-radius: 14px; }
            .meta { padding: 11px; }
        }
    </style>
</head>
<body>
<header>
    <div class="shell topbar">
        <div class="brand">
            <div class="brand-mark"></div>
            <span>{{ config('app.name', 'Casino') }}</span>
        </div>
        <div class="search">
            <input id="search" type="search" placeholder="Search games or providers…" autocomplete="off">
        </div>
    </div>
</header>

<main class="shell">
    <section class="hero">
        <h1>Play your favourite games.</h1>
        <p>Browse by provider, search instantly, and launch through the existing secure session API.</p>
    </section>

    <div class="section-head">
        <h2>Providers</h2>
        <div class="status" id="providerStatus">Loading…</div>
    </div>

    <div class="providers" id="providers">
        <button class="provider active" data-provider="">All</button>
    </div>

    <div class="section-head">
        <h2>Games</h2>
        <div class="status" id="gameStatus">Loading…</div>
    </div>

    <section class="games" id="games">
        <div class="empty">Loading games…</div>
    </section>

    <div class="load-more-wrap">
        <button class="load-more" id="loadMore" type="button" disabled>Load more</button>
    </div>
</main>

<div class="toast" id="toast"></div>

<script>
(() => {
    const state = {
        provider: '',
        page: 1,
        lastPage: 1,
        games: [],
        search: '',
        loading: false,
    };

    const gamesEl = document.getElementById('games');
    const providersEl = document.getElementById('providers');
    const gameStatusEl = document.getElementById('gameStatus');
    const providerStatusEl = document.getElementById('providerStatus');
    const loadMoreEl = document.getElementById('loadMore');
    const searchEl = document.getElementById('search');
    const toastEl = document.getElementById('toast');

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function toast(message) {
        toastEl.textContent = message;
        toastEl.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    async function json(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    function filteredGames() {
        const query = state.search.trim().toLowerCase();
        if (!query) return state.games;

        return state.games.filter(game => {
            return String(game.name || '').toLowerCase().includes(query)
                || String(game.provider || '').toLowerCase().includes(query)
                || String(game.slug || '').toLowerCase().includes(query);
        });
    }

    function renderGames() {
        const list = filteredGames();
        gameStatusEl.textContent = `${list.length} shown`;

        if (!list.length) {
            gamesEl.innerHTML = '<div class="empty">No games found.</div>';
            return;
        }

        gamesEl.innerHTML = list.map(game => {
            const name = escapeHtml(game.name || game.slug || 'Game');
            const provider = escapeHtml(game.provider || 'Unknown provider');
            const slug = escapeHtml(game.slug || '');
            const img = escapeHtml(game.img || game.image || '');

            return `
                <button class="card" type="button" data-slug="${slug}" aria-label="Play ${name}">
                    <div class="thumb">
                        ${img
                            ? `<img loading="lazy" src="${img}" alt="" referrerpolicy="no-referrer" onerror="this.outerHTML='<div class=&quot;thumb-fallback&quot;>Image unavailable</div>'">`
                            : '<div class="thumb-fallback">Image unavailable</div>'}
                    </div>
                    <div class="meta">
                        <div class="game-name" title="${name}">${name}</div>
                        <div class="provider-name">${provider}</div>
                    </div>
                </button>
            `;
        }).join('');
    }

    async function loadProviders() {
        try {
            const payload = await json('/api/categories?limit=100');
            const providers = Array.isArray(payload?.data) ? payload.data : [];

            providersEl.innerHTML =
                '<button class="provider active" data-provider="">All</button>' +
                providers.map(item => {
                    const slug = escapeHtml(item.slug || item.provider || '');
                    const name = escapeHtml(item.name || item.slug || 'Provider');
                    const count = Number(item.eligible_games || item.game_count || 0);
                    return `<button class="provider" data-provider="${slug}">${name}${count ? ` · ${count}` : ''}</button>`;
                }).join('');

            providerStatusEl.textContent = `${providers.length} providers`;
        } catch (error) {
            providerStatusEl.textContent = 'Unavailable';
            console.error(error);
        }
    }

    async function loadGames({ append = false } = {}) {
        if (state.loading) return;

        state.loading = true;
        loadMoreEl.disabled = true;
        if (!append) {
            gamesEl.innerHTML = '<div class="empty">Loading games…</div>';
        }

        try {
            const params = new URLSearchParams({
                limit: '100',
                page: String(state.page),
            });

            if (state.provider) {
                params.set('provider', state.provider);
            }

            const payload = await json('/api/games?' + params.toString());
            const incoming = Array.isArray(payload?.data) ? payload.data : [];

            state.lastPage = Number(payload?.last_page || state.page);
            state.games = append ? state.games.concat(incoming) : incoming;

            renderGames();

            loadMoreEl.hidden = state.page >= state.lastPage;
            loadMoreEl.disabled = state.page >= state.lastPage;
        } catch (error) {
            if (!append) {
                gamesEl.innerHTML = `<div class="error">Failed to load games (${escapeHtml(error.message)}).</div>`;
            }
            gameStatusEl.textContent = 'API error';
            toast('Could not load games.');
            console.error(error);
        } finally {
            state.loading = false;
        }
    }

    providersEl.addEventListener('click', event => {
        const button = event.target.closest('[data-provider]');
        if (!button) return;

        providersEl.querySelectorAll('.provider').forEach(el => el.classList.remove('active'));
        button.classList.add('active');

        state.provider = button.dataset.provider || '';
        state.page = 1;
        state.games = [];
        loadGames();
    });

    gamesEl.addEventListener('click', event => {
        const card = event.target.closest('[data-slug]');
        if (!card) return;

        const slug = card.dataset.slug;
        if (!slug) return;

        window.location.href = '/api/play/' + encodeURIComponent(slug);
    });

    loadMoreEl.addEventListener('click', () => {
        if (state.page >= state.lastPage || state.loading) return;
        state.page += 1;
        loadGames({ append: true });
    });

    searchEl.addEventListener('input', () => {
        state.search = searchEl.value;
        renderGames();
    });

    Promise.allSettled([loadProviders(), loadGames()]);
})();
</script>
</body>
</html>
