<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mafia Tracker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-slate-100">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <header class="flex items-center justify-between mb-8">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-slate-100">🔪 Mafia Tracker</a>
            @isset($game)
                <div class="text-sm text-slate-400">Round <span class="font-bold text-amber-400">{{ $game->round_number }}</span></div>
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
</body>
</html>
