<?php

namespace App\Http\Controllers;

use App\Models\MafiaGame;
use App\Models\MafiaPlayer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MafiaController extends Controller
{
    public function index()
    {
        $recentGames = MafiaGame::whereIn('status', ['setup', 'active'])
            ->latest()
            ->limit(5)
            ->get();

        return view('welcome', compact('recentGames'));
    }

    public function create()
    {
        return view('mafia.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'names' => ['required', 'string'],
            'mafia_total' => ['required', 'integer', 'min:1'],
            'citizen_total' => ['required', 'integer', 'min:1'],
        ]);

        $names = collect(preg_split('/\r\n|\r|\n/', trim($data['names'])))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->values();

        if ($names->count() < 3) {
            return back()->withErrors(['names' => 'Enter at least 3 player names, one per line.'])->withInput();
        }

        if ($data['mafia_total'] + $data['citizen_total'] !== $names->count()) {
            return back()->withErrors([
                'mafia_total' => "Mafia + Citizens ({$data['mafia_total']} + {$data['citizen_total']}) must add up to the number of players ({$names->count()}).",
            ])->withInput();
        }

        if ($data['mafia_total'] >= $data['citizen_total']) {
            return back()->withErrors(['mafia_total' => 'Mafia count must be less than Citizen count to start a winnable game.'])->withInput();
        }

        $game = MafiaGame::create([
            'status' => 'active',
            'mafia_total' => $data['mafia_total'],
            'citizen_total' => $data['citizen_total'],
            'round_number' => 1,
        ]);

        $names->each(function (string $name, int $index) use ($game) {
            $game->players()->create([
                'name' => $name,
                'sort_order' => $index,
            ]);
        });

        return redirect()->route('mafia.show', $game);
    }

    public function show(MafiaGame $game)
    {
        $game->load(['players', 'rounds.eliminatedPlayer']);

        if ($game->status === 'ended') {
            return view('mafia.ended', compact('game'));
        }

        return view('mafia.dashboard', compact('game'));
    }

    public function vote(Request $request, MafiaGame $game)
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'delta' => ['required', 'integer', 'in:-1,1'],
        ]);

        $player = $game->players()->where('is_eliminated', false)->findOrFail($data['player_id']);
        $player->votes = max(0, $player->votes + $data['delta']);
        $player->save();

        if ($request->wantsJson()) {
            return response()->json(['id' => $player->id, 'votes' => $player->votes]);
        }

        return redirect()->route('mafia.show', $game);
    }

    public function resetVotes(MafiaGame $game): RedirectResponse
    {
        $game->players()->where('is_eliminated', false)->update(['votes' => 0]);

        return redirect()->route('mafia.show', $game);
    }

    public function eliminate(Request $request, MafiaGame $game): RedirectResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'role' => ['required', 'in:mafia,citizen'],
        ]);

        $player = $game->players()->where('is_eliminated', false)->findOrFail($data['player_id']);

        $voteSnapshot = $game->players()
            ->where('is_eliminated', false)
            ->pluck('votes', 'id');

        $player->update([
            'is_eliminated' => true,
            'eliminated_round' => $game->round_number,
            'revealed_role' => $data['role'],
        ]);

        $game->rounds()->create([
            'round_number' => $game->round_number,
            'eliminated_player_id' => $player->id,
            'eliminated_role' => $data['role'],
            'vote_snapshot' => $voteSnapshot,
        ]);

        $game->players()->where('is_eliminated', false)->update(['votes' => 0]);
        $game->refresh();
        $game->load('players');

        $winner = $game->checkWinner();

        if ($winner) {
            $game->update(['status' => 'ended', 'winner' => $winner]);
        } else {
            $game->increment('round_number');
        }

        return redirect()->route('mafia.show', $game);
    }

    public function undo(MafiaGame $game): RedirectResponse
    {
        $lastRound = $game->rounds()->whereNotNull('eliminated_player_id')->first();

        if (! $lastRound) {
            return redirect()->route('mafia.show', $game);
        }

        $player = MafiaPlayer::find($lastRound->eliminated_player_id);

        if ($player) {
            $player->update([
                'is_eliminated' => false,
                'eliminated_round' => null,
                'revealed_role' => null,
            ]);
        }

        foreach ($lastRound->vote_snapshot ?? [] as $playerId => $votes) {
            MafiaPlayer::where('id', $playerId)->update(['votes' => $votes]);
        }

        $game->update([
            'status' => 'active',
            'winner' => null,
            'round_number' => $lastRound->round_number,
        ]);

        $lastRound->delete();

        return redirect()->route('mafia.show', $game);
    }

    public function end(MafiaGame $game): RedirectResponse
    {
        $game->update(['status' => 'ended', 'winner' => $game->winner ?? 'none']);

        return redirect()->route('mafia.show', $game);
    }

    public function restart(MafiaGame $game): RedirectResponse
    {
        $game->rounds()->delete();

        $game->players()->update([
            'is_eliminated' => false,
            'eliminated_round' => null,
            'revealed_role' => null,
            'votes' => 0,
        ]);

        $game->update([
            'status' => 'active',
            'round_number' => 1,
            'winner' => null,
        ]);

        return redirect()->route('mafia.show', $game);
    }
}
