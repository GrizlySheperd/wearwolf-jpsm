<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Werewolf')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        @keyframes pulse-slow { 0%,100%{opacity:1} 50%{opacity:.55} }
        .animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-slate-100"
      @isset($game) data-poll-url="{{ route('games.status', $game->code) }}" data-poll-token="{{ $game->stateToken() }}" @endisset>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <header class="flex items-center justify-between mb-8">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-slate-100">🐺 Werewolf</a>
            @isset($game)
                <div class="text-sm text-slate-400">Room code
                    <span class="font-mono text-lg font-bold text-amber-400 tracking-widest">{{ $game->code }}</span>
                </div>
            @endisset
        </header>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/40 bg-red-950/60 px-4 py-3 text-red-200 text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        (function () {
            var body = document.body;
            var url = body.getAttribute('data-poll-url');
            if (!url) return;
            var token = body.getAttribute('data-poll-token');
            setInterval(function () {
                fetch(url).then(function (r) { return r.json(); }).then(function (data) {
                    if (data.token && data.token !== token) {
                        window.location.reload();
                    }
                }).catch(function () {});
            }, 2500);
        })();
    </script>
</body>
</html>
