<div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
    <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">
        Players ({{ $game->alivePlayers()->count() }} alive / {{ $game->players->count() }} total)
    </h2>
    <ul class="grid grid-cols-2 gap-2 text-sm">
        @foreach ($game->players as $p)
            <li class="flex items-center justify-between rounded-lg px-3 py-2 {{ $p->is_alive ? 'bg-slate-800/70' : 'bg-slate-800/20 line-through text-slate-500' }}">
                <span>{{ $p->name }} {{ $p->id === $player->id ? '(you)' : '' }}</span>
                @if (! $p->is_alive)
                    <span>💀</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
