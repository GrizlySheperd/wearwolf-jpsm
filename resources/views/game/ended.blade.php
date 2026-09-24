@extends('layout')

@section('title', 'Game Over — Werewolf')

@section('content')
    <div class="text-center mb-6 rounded-2xl p-8 border
        {{ $game->winner === 'werewolves' ? 'bg-red-950/50 border-red-800' : 'bg-emerald-950/50 border-emerald-800' }}">
        <h1 class="text-3xl font-black mb-2">
            {{ $game->winner === 'werewolves' ? '🐺 The Werewolves Win!' : '🎉 The Villagers Win!' }}
        </h1>
        <p class="text-slate-400 text-sm">
            {{ $game->winner === 'werewolves' ? 'The werewolves have overrun the village.' : 'Every werewolf has been eliminated.' }}
        </p>
    </div>

    @include('partials.role-card', ['player' => $player])

    <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6">
        <h2 class="font-semibold mb-3">Final roles</h2>
        <ul class="grid grid-cols-2 gap-2 text-sm">
            @foreach ($game->players as $p)
                @php($def = \App\Support\Roles::definition($p->role))
                <li class="flex items-center justify-between rounded-lg px-3 py-2 {{ $p->is_alive ? 'bg-slate-800/70' : 'bg-slate-800/20 text-slate-400' }}">
                    <span>{{ $def['icon'] }} {{ $p->name }}</span>
                    <span class="text-xs uppercase tracking-wide">{{ $def['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    @include('partials.log', ['logs' => $logs ?? $game->publicLogsFor($player)])

    @if ($player->is_host)
        <form method="POST" action="{{ route('games.reset', $game->code) }}">
            @csrf
            <button class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 transition">
                Play again with the same players
            </button>
        </form>
    @else
        <p class="text-center text-slate-400">Waiting for the host to start a new round&hellip;</p>
    @endif

    <form method="POST" action="{{ route('games.leave', $game->code) }}" class="mt-6 text-center">
        @csrf
        <button class="text-sm text-slate-500 hover:text-slate-300 underline">Leave game</button>
    </form>
@endsection
