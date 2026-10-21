@extends('layout')

@section('title', 'Round '.$game->round_number.' — Mafia Tracker')

@section('content')
    @php
        $alive = $game->players->where('is_eliminated', false)->sortBy('sort_order')->values();
        $eliminated = $game->eliminatedPlayers();
        $maxVotes = $alive->max('votes');
    @endphp

    <div class="grid grid-cols-3 gap-3 mb-6 text-center">
        <div class="bg-red-950/50 border border-red-800 rounded-xl py-3">
            <div class="text-2xl font-black text-red-300">{{ $game->mafiaRemaining() }}</div>
            <div class="text-xs uppercase tracking-wide text-red-400/80">Mafia left</div>
        </div>
        <div class="bg-emerald-950/50 border border-emerald-800 rounded-xl py-3">
            <div class="text-2xl font-black text-emerald-300">{{ $game->citizensRemaining() }}</div>
            <div class="text-xs uppercase tracking-wide text-emerald-400/80">Citizens left</div>
        </div>
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl py-3">
            <div class="text-2xl font-black text-slate-200">{{ $alive->count() }}</div>
            <div class="text-xs uppercase tracking-wide text-slate-400">Alive</div>
        </div>
    </div>

    <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Vote tally &mdash; Round {{ $game->round_number }}</h2>
            <form method="POST" action="{{ route('mafia.resetVotes', $game) }}">
                @csrf
                <button class="text-xs text-slate-500 hover:text-slate-300 underline">Reset votes</button>
            </form>
        </div>

        <div class="space-y-2">
            @foreach ($alive as $player)
                <div class="rounded-xl border {{ $player->votes > 0 && $player->votes === $maxVotes ? 'border-amber-500 bg-amber-500/10' : 'border-slate-700 bg-slate-800/60' }} p-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium truncate">{{ $player->name }}</span>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="castVote({{ $player->id }}, -1)"
                                    class="w-9 h-9 rounded-lg bg-slate-700 hover:bg-slate-600 font-bold text-lg leading-none">&minus;</button>
                            <span id="votes-{{ $player->id }}" class="w-8 text-center text-xl font-black tabular-nums">{{ $player->votes }}</span>
                            <button type="button" onclick="castVote({{ $player->id }}, 1)"
                                    class="w-9 h-9 rounded-lg bg-slate-700 hover:bg-slate-600 font-bold text-lg leading-none">+</button>

                            <button type="button" onclick="toggleReveal({{ $player->id }})"
                                    class="ml-2 text-xs rounded-lg bg-red-600/80 hover:bg-red-600 px-3 py-2 font-semibold">
                                Eliminate
                            </button>
                        </div>
                    </div>

                    <div id="reveal-{{ $player->id }}" class="hidden mt-3 pt-3 border-t border-slate-700">
                        <p class="text-xs text-slate-400 mb-2">Reveal {{ $player->name }}'s role:</p>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('mafia.eliminate', $game) }}">
                                @csrf
                                <input type="hidden" name="player_id" value="{{ $player->id }}">
                                <input type="hidden" name="role" value="mafia">
                                <button class="rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-bold px-4 py-2">🔪 Mafia</button>
                            </form>
                            <form method="POST" action="{{ route('mafia.eliminate', $game) }}">
                                @csrf
                                <input type="hidden" name="player_id" value="{{ $player->id }}">
                                <input type="hidden" name="role" value="citizen">
                                <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold px-4 py-2">🧑 Citizen</button>
                            </form>
                            <button type="button" onclick="toggleReveal({{ $player->id }})" class="text-xs text-slate-500 hover:text-slate-300 ml-auto">Cancel</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($eliminated->isNotEmpty())
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 mb-6">
            <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">Eliminated</h2>
            <ul class="space-y-1 text-sm">
                @foreach ($eliminated as $player)
                    <li class="flex items-center justify-between text-slate-400">
                        <span>Round {{ $player->eliminated_round }} &middot; {{ $player->name }}</span>
                        <span class="{{ $player->revealed_role === 'mafia' ? 'text-red-400' : 'text-emerald-400' }} font-semibold">
                            {{ $player->revealed_role === 'mafia' ? '🔪 Mafia' : '🧑 Citizen' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between">
        @if ($eliminated->isNotEmpty())
            <form method="POST" action="{{ route('mafia.undo', $game) }}">
                @csrf
                <button class="text-sm text-slate-500 hover:text-slate-300 underline">Undo last elimination</button>
            </form>
        @else
            <span></span>
        @endif

        <form method="POST" action="{{ route('mafia.end', $game) }}" onsubmit="return confirm('End the game now?');">
            @csrf
            <button class="text-sm text-slate-500 hover:text-red-400 underline">End game</button>
        </form>
    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        function castVote(playerId, delta) {
            const el = document.getElementById('votes-' + playerId);
            const next = Math.max(0, parseInt(el.textContent, 10) + delta);
            el.textContent = next;

            fetch('{{ route('mafia.vote', $game) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ player_id: playerId, delta: delta }),
            })
                .then(r => r.json())
                .then(data => { el.textContent = data.votes; })
                .catch(() => {});
        }

        function toggleReveal(playerId) {
            document.getElementById('reveal-' + playerId).classList.toggle('hidden');
        }
    </script>
@endsection
