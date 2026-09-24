@extends('layout')

@section('title', 'Lobby — Werewolf')

@section('content')
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold">Waiting in the lobby&hellip;</h1>
        <p class="text-slate-400 text-sm mt-1">Share the room code with your friends so they can join.</p>
        <div class="mt-3 inline-block font-mono text-3xl tracking-[0.4em] bg-slate-800 border border-slate-600 rounded-xl px-6 py-3 text-amber-400">
            {{ $game->code }}
        </div>
    </div>

    <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6">
        <h2 class="font-semibold mb-3">Players ({{ $game->players->count() }})</h2>
        <ul class="space-y-2">
            @foreach ($game->players as $p)
                <li class="flex items-center justify-between bg-slate-800/70 rounded-lg px-3 py-2">
                    <span>{{ $p->name }}</span>
                    <span class="flex items-center gap-2">
                        @if ($p->is_host)
                            <span class="text-xs uppercase tracking-wide bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded">Host</span>
                        @endif
                        @if ($p->id === $player->id)
                            <span class="text-xs uppercase tracking-wide bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded">You</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($player->is_host)
        <form method="POST" action="{{ route('games.start', $game->code) }}">
            @csrf
            <button @if($game->players->count() < 4) disabled @endif
                class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-bold py-3 transition">
                Start Game
            </button>
        </form>
        @if ($game->players->count() < 4)
            <p class="text-center text-slate-500 text-sm mt-2">Need at least 4 players to start ({{ $game->players->count() }}/4).</p>
        @endif
    @else
        <p class="text-center text-slate-400 animate-pulse-slow">Waiting for the host to start the game&hellip;</p>
    @endif

    <form method="POST" action="{{ route('games.leave', $game->code) }}" class="mt-6 text-center">
        @csrf
        <button class="text-sm text-slate-500 hover:text-slate-300 underline">Leave game</button>
    </form>
@endsection
