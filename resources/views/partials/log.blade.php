@if ($logs->isNotEmpty())
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 mb-6">
        <h2 class="font-semibold mb-3 text-sm uppercase tracking-wide text-slate-500">Village log</h2>
        <ul class="text-sm space-y-2 text-slate-300">
            @foreach ($logs as $log)
                <li class="{{ str_starts_with($log->visibility, 'player:') ? 'text-purple-300' : '' }}">{{ $log->message }}</li>
            @endforeach
        </ul>
    </div>
@endif
