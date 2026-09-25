@extends('layout')

@section('title', 'Join Game — Werewolf')

@section('content')
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold mb-2">You're joining a game</h1>
        <p class="text-slate-400 text-sm">Room code</p>
        <div class="mt-2 inline-block font-mono text-3xl tracking-[0.4em] bg-slate-800 border border-slate-600 rounded-xl px-6 py-3 text-amber-400">
            {{ $game->code }}
        </div>
    </div>

    <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 max-w-sm mx-auto shadow-xl">
        <form method="POST" action="{{ route('games.join') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="code" value="{{ $game->code }}">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Your name</label>
                <input name="name" required maxlength="30" autofocus placeholder="e.g. Sam"
                       class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <button class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2 transition">
                Join Room
            </button>
        </form>
    </div>
@endsection
