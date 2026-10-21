@extends('layout')

@section('title', 'Game Over — Mafia Tracker')

@section('content')
    @php
        $eliminated = $game->eliminatedPlayers();
        $survivors = $game->players->where('is_eliminated', false)->sortBy('sort_order')->values();
    @endphp

    <div class="text-center mb-6 rounded-2xl p-8 border
        {{ $game->winner === 'mafia' ? 'bg-red-950/50 border-red-800' : ($game->winner === 'citizens' ? 'bg-emerald-950/50 border-emerald-800' : 'bg-slate-900/60 border-slate-700') }}">
        <h1 class="text-3xl font-black mb-2">
            @if ($game->winner === 'mafia')
                🔪 Mafia Wins!
            @elseif ($game->winner === 'citizens')
                🎉 Citizens Win!
            @else
                Game Ended
            @endif
        </h1>
        <p class="text-slate-400 text-sm">Mafia remaining: {{ $game->mafiaRemaining() }} &middot; Citizens remaining: {{ $game->citizensRemaining() }}</p>
    </div>

    @if ($survivors->isNotEmpty())
        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-5 mb-6">
            <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">Survivors (unrevealed)</h2>
            <ul class="grid grid-cols-2 gap-2 text-sm">
                @foreach ($survivors as $p)
                    <li class="rounded-lg bg-slate-800/70 px-3 py-2">{{ $p->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($eliminated->isNotEmpty())
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 mb-6">
            <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">Elimination history</h2>
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

    <form method="POST" action="{{ route('mafia.restart', $game) }}">
        @csrf
        <button class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 transition">
            Play again with the same players
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-300 underline">Back to home</a>
    </div>
@endsection
