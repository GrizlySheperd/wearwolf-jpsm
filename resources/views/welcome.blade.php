@extends('layout')

@section('title', 'Werewolf — Play Online')

@section('content')
    <div class="text-center mb-10">
        <h1 class="text-4xl font-black mb-2">🐺 Werewolf</h1>
        <p class="text-slate-400">A social deduction game of villagers, werewolves, a doctor and a seer.
            Gather your friends on their own phones, create a room, and share the code.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 shadow-xl">
            <h2 class="text-xl font-bold mb-4">Create a new game</h2>
            <form method="POST" action="{{ route('games.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Your name</label>
                    <input name="name" required maxlength="30" placeholder="e.g. Alex"
                           class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <button class="w-full rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2 transition">
                    Create Room
                </button>
            </form>
        </div>

        <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 shadow-xl">
            <h2 class="text-xl font-bold mb-4">Join a game</h2>
            <form method="POST" action="{{ route('games.join') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Your name</label>
                    <input name="name" required maxlength="30" placeholder="e.g. Sam"
                           class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Room code</label>
                    <input name="code" required maxlength="8" placeholder="e.g. ABCDE" style="text-transform:uppercase"
                           class="w-full rounded-lg bg-slate-800 border border-slate-600 px-3 py-2 tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 text-slate-950 font-bold py-2 transition">
                    Join Room
                </button>
            </form>
        </div>
    </div>

    <div class="mt-10 bg-slate-900/50 border border-slate-800 rounded-2xl p-6 text-sm text-slate-400 space-y-2">
        <h3 class="text-slate-200 font-semibold mb-2">How to play</h3>
        <p>Every player gets a secret role. Each night, the Werewolves choose a victim, the Doctor chooses someone to protect, and the Seer investigates a player. Each day, the whole village discusses and votes to eliminate a suspect. Villagers win by eliminating all werewolves; werewolves win once they equal or outnumber the villagers.</p>
    </div>
@endsection
