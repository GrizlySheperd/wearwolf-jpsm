@extends('layout')

@section('title', 'New Game — Mafia Tracker')

@section('content')
    <h1 class="text-2xl font-bold text-center mb-6">Set up a new game</h1>

    <form method="POST" action="{{ route('mafia.store') }}" class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 space-y-5 shadow-xl">
        @csrf
        <div>
            <label class="block text-sm text-slate-400 mb-1">Player names <span class="text-slate-500">(one per line)</span></label>
            <textarea name="names" rows="10" required placeholder="Alex&#10;Sam&#10;Jordan&#10;Casey&#10;Riley"
                      class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">{{ old('names') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Number of Mafia</label>
                <input type="number" name="mafia_total" min="1" required value="{{ old('mafia_total') }}"
                       class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Number of Citizens</label>
                <input type="number" name="citizen_total" min="1" required value="{{ old('citizen_total') }}"
                       class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
        <p class="text-xs text-slate-500">Mafia + Citizens must add up to the number of player names entered above.</p>

        <button class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 transition">
            Start Game
        </button>
    </form>
@endsection
