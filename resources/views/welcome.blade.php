@extends('layout')

@section('title', 'Mafia Tracker')

@section('content')
    <div class="text-center mb-10">
        <h1 class="text-4xl font-black mb-2">🔪 Mafia Tracker</h1>
        <p class="text-slate-400">A moderator's scoreboard for in-person Mafia / Werewolf games. Enter the players, set how many are Mafia, then tally votes each round and reveal who's eliminated.</p>
    </div>

    <div class="text-center mb-10">
        <a href="{{ route('mafia.create') }}"
           class="inline-block rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-8 py-3 transition">
            Start New Game
        </a>
    </div>

    @if ($recentGames->isNotEmpty())
        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6">
            <h2 class="font-semibold mb-3">Resume a game in progress</h2>
            <ul class="space-y-2">
                @foreach ($recentGames as $g)
                    <li>
                        <a href="{{ route('mafia.show', $g) }}" class="flex items-center justify-between bg-slate-800/70 hover:bg-slate-800 rounded-lg px-3 py-2 transition">
                            <span>{{ $g->players()->count() }} players &middot; Round {{ $g->round_number }}</span>
                            <span class="text-xs uppercase tracking-wide text-slate-400">{{ $g->status }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
