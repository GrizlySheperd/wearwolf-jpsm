@php($def = \App\Support\Roles::definition($player->role))
<div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-6 mb-6">
    <div class="flex items-center gap-3 mb-2">
        <span class="text-3xl">{{ $def['icon'] }}</span>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Your role</div>
            <div class="text-xl font-bold">{{ $def['label'] }}</div>
        </div>
        <span class="ml-auto text-xs uppercase tracking-wide px-2 py-1 rounded
            {{ $def['team'] === 'werewolves' ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300' }}">
            Team {{ ucfirst($def['team']) }}
        </span>
    </div>
    <p class="text-sm text-slate-400">{{ $def['description'] }}</p>
    @if (! $player->is_alive)
        <p class="mt-3 text-sm text-red-400 font-semibold">💀 You have died: {{ $player->death_reason }}. You can keep watching the game.</p>
    @endif
</div>
