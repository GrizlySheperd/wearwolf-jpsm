@extends('layout')

@section('title', 'Night '.$game->night_number.' — Werewolf')

@section('content')
    @include('partials.role-card', ['player' => $player])

    <div class="bg-indigo-950/60 border border-indigo-800 rounded-2xl p-6 mb-6 text-center">
        <h1 class="text-2xl font-bold">🌙 Night {{ $game->night_number }}</h1>
        <p class="text-slate-400 text-sm mt-1">The village is asleep. Those with a role must act.</p>
    </div>

    @if ($player->is_alive && in_array($player->role, ['werewolf', 'doctor', 'seer']))
        @php
            $actionLabel = ['werewolf' => 'Choose a victim', 'doctor' => 'Choose someone to protect', 'seer' => 'Choose someone to investigate'][$player->role];
            $buttonLabel = ['werewolf' => 'Attack', 'doctor' => 'Protect', 'seer' => 'Investigate'][$player->role];
            $targets = $game->players->filter(function ($p) use ($player) {
                if (! $p->is_alive) return false;
                if ($player->role !== 'doctor' && $p->id === $player->id) return false;
                return true;
            });
        @endphp

        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold mb-3">{{ $actionLabel }}</h2>

            @if ($myAction)
                <p class="text-emerald-400 text-sm mb-3">
                    ✅ You chose <strong>{{ optional($game->players->firstWhere('id', $myAction->target_id))->name }}</strong>.
                    You can change your mind below until night ends.
                </p>
            @endif

            <form method="POST" action="{{ route('games.nightAction', $game->code) }}" class="space-y-2">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($targets as $t)
                        <button name="target_id" value="{{ $t->id }}"
                            class="rounded-lg border px-3 py-2 text-left transition
                                {{ $myAction && (int) $myAction->target_id === $t->id ? 'bg-amber-500 text-slate-950 border-amber-400 font-bold' : 'bg-slate-800 border-slate-600 hover:border-amber-500' }}">
                            {{ $t->name }} {{ $t->id === $player->id ? '(yourself)' : '' }}
                        </button>
                    @endforeach
                </div>
            </form>
            <p class="text-xs text-slate-500 mt-3">Tap a name to {{ strtolower($buttonLabel) }} them.</p>
        </div>
    @elseif ($player->is_alive)
        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6 text-center">
            <p class="text-slate-400 animate-pulse-slow">You have no night action. Wait for morning&hellip;</p>
        </div>
    @endif

    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 mb-6">
        <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">Night progress</h2>
        <ul class="text-sm space-y-1 text-slate-300">
            <li>🐺 Werewolves acted: {{ $wolvesActed }}/{{ $wolvesTotal }}</li>
            @if ($doctorAlive)
                <li>💉 Doctor acted: {{ $doctorActed ? 'Yes' : 'Not yet' }}</li>
            @endif
            @if ($seerAlive)
                <li>🔮 Seer acted: {{ $seerActed ? 'Yes' : 'Not yet' }}</li>
            @endif
        </ul>

        @if ($player->is_host)
            <form method="POST" action="{{ route('games.resolveNight', $game->code) }}" class="mt-4">
                @csrf
                <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 transition">
                    Force resolve night (host)
                </button>
            </form>
        @endif
    </div>

    @include('partials.log', ['logs' => $logs])
    @include('partials.roster', ['game' => $game, 'player' => $player])
@endsection
