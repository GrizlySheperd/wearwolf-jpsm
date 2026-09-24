@extends('layout')

@section('title', 'Day '.$game->day_number.' — Werewolf')

@section('content')
    @include('partials.role-card', ['player' => $player])

    <div class="bg-amber-950/40 border border-amber-800 rounded-2xl p-6 mb-6 text-center">
        <h1 class="text-2xl font-bold">☀️ Day {{ $game->day_number }}</h1>
        <p class="text-slate-400 text-sm mt-1">Discuss out loud with your group, then cast your vote to lynch a suspect.</p>
    </div>

    @include('partials.log', ['logs' => $logs])

    @if ($player->is_alive)
        @php
            $targets = $game->players->filter(fn ($p) => $p->is_alive && $p->id !== $player->id);
        @endphp
        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold mb-3">Cast your vote</h2>

            @if ($myVote)
                <p class="text-emerald-400 text-sm mb-3">
                    ✅ You voted for
                    <strong>{{ $myVote->target_id ? optional($game->players->firstWhere('id', $myVote->target_id))->name : 'no one (skip)' }}</strong>.
                    You can change your vote below until the day ends.
                </p>
            @endif

            <form method="POST" action="{{ route('games.vote', $game->code) }}" class="space-y-2">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($targets as $t)
                        <button name="target_id" value="{{ $t->id }}"
                            class="rounded-lg border px-3 py-2 text-left transition
                                {{ $myVote && (int) $myVote->target_id === $t->id ? 'bg-red-500 text-slate-950 border-red-400 font-bold' : 'bg-slate-800 border-slate-600 hover:border-red-500' }}">
                            {{ $t->name }}
                        </button>
                    @endforeach
                    <button name="target_id" value=""
                        class="rounded-lg border px-3 py-2 text-left transition col-span-2
                            {{ $myVote && ! $myVote->target_id ? 'bg-slate-500 text-slate-950 border-slate-400 font-bold' : 'bg-slate-800 border-slate-600 hover:border-slate-400' }}">
                        Skip vote
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 mb-6">
        <p class="text-sm text-slate-300">🗳️ Votes cast: {{ $votesIn }}/{{ $aliveCount }}</p>

        @if ($player->is_host)
            <form method="POST" action="{{ route('games.resolveDay', $game->code) }}" class="mt-4">
                @csrf
                <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 transition">
                    Force resolve day (host)
                </button>
            </form>
        @endif
    </div>

    @include('partials.roster', ['game' => $game, 'player' => $player])
@endsection
