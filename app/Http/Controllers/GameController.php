<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function joinForm(string $code)
    {
        $game = Game::where('code', strtoupper($code))->first();

        if (! $game) {
            return redirect()->route('home')->withErrors(['code' => 'No game found with that code.']);
        }

        if ($this->currentPlayer($game)) {
            return redirect()->route('games.show', $game->code);
        }

        if ($game->status !== 'lobby') {
            return redirect()->route('home')->withErrors(['code' => 'That game has already started.']);
        }

        return view('join', ['game' => $game]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30'],
        ]);

        do {
            $code = strtoupper(Str::random(5));
        } while (Game::where('code', $code)->exists());

        $game = Game::create(['code' => $code, 'status' => 'lobby']);

        $player = $game->players()->create([
            'name' => $data['name'],
            'token' => (string) Str::uuid(),
            'is_host' => true,
        ]);

        session(["player_token.{$game->code}" => $player->token]);

        return redirect()->route('games.show', $game->code);
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'max:8'],
        ]);

        $code = strtoupper($data['code']);
        $game = Game::where('code', $code)->first();

        if (! $game) {
            return back()->withErrors(['code' => 'No game found with that code.'])->withInput();
        }

        $existing = $this->currentPlayer($game);
        if ($existing) {
            return redirect()->route('games.show', $game->code);
        }

        if ($game->status !== 'lobby') {
            return back()->withErrors(['code' => 'That game has already started.'])->withInput();
        }

        $player = $game->players()->create([
            'name' => $data['name'],
            'token' => (string) Str::uuid(),
            'is_host' => false,
        ]);

        session(["player_token.{$game->code}" => $player->token]);

        return redirect()->route('games.show', $game->code);
    }

    public function show(string $code)
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player) {
            return redirect()->route('home')->withErrors(['code' => 'Join the game with your name first.']);
        }

        $game->load('players');

        return match ($game->status) {
            'lobby' => view('game.lobby', compact('game', 'player')),
            'night' => view('game.night', $this->nightViewData($game, $player)),
            'day' => view('game.day', $this->dayViewData($game, $player)),
            'ended' => view('game.ended', compact('game', 'player')),
            default => abort(404),
        };
    }

    public function status(string $code)
    {
        $game = $this->findGame($code);

        return response()->json(['token' => $game->stateToken()]);
    }

    public function start(string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || ! $player->is_host || $game->status !== 'lobby') {
            return back();
        }

        $players = $game->players;

        if ($players->count() < 4) {
            return back()->withErrors(['start' => 'You need at least 4 players to start.']);
        }

        Roles::assign($players);

        $game->update(['status' => 'night', 'night_number' => 1, 'day_number' => 0, 'winner' => null]);
        $game->addLog('Night falls on the village for the first time. Everyone with a role should act now.');

        return redirect()->route('games.show', $game->code);
    }

    public function nightAction(Request $request, string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || $game->status !== 'night' || ! $player->is_alive) {
            return back();
        }

        $data = $request->validate([
            'target_id' => ['nullable', 'integer'],
        ]);

        $actionMap = [
            'werewolf' => 'kill',
            'doctor' => 'save',
            'seer' => 'inspect',
        ];

        $action = $actionMap[$player->role] ?? null;

        if (! $action) {
            return back();
        }

        $target = $data['target_id'] ? $game->players->firstWhere('id', $data['target_id']) : null;

        if (! $target || ! $target->is_alive) {
            return back()->withErrors(['target_id' => 'Choose a valid living player.']);
        }

        if ($action !== 'save' && $target->id === $player->id) {
            return back()->withErrors(['target_id' => 'You cannot target yourself.']);
        }

        $game->nightActions()->updateOrCreate(
            [
                'night_number' => $game->night_number,
                'actor_id' => $player->id,
                'action' => $action,
            ],
            ['target_id' => $target->id]
        );

        if ($action === 'inspect') {
            $result = $target->role === 'werewolf' ? 'IS a Werewolf' : 'is NOT a Werewolf';
            $game->addLog("🔮 Your vision reveals that {$target->name} {$result}.", "player:{$player->id}");
        }

        $this->autoResolveNightIfReady($game->fresh(['players']));

        return redirect()->route('games.show', $game->code);
    }

    public function vote(Request $request, string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || $game->status !== 'day' || ! $player->is_alive) {
            return back();
        }

        $request->merge(['target_id' => $request->input('target_id') ?: null]);

        $data = $request->validate([
            'target_id' => ['nullable', 'integer'],
        ]);

        $target = $data['target_id'] ?? null;
        $target = $target ? $game->players->firstWhere('id', $target) : null;

        if ($data['target_id'] && (! $target || ! $target->is_alive)) {
            return back()->withErrors(['target_id' => 'Choose a valid living player.']);
        }

        $game->votes()->updateOrCreate(
            [
                'day_number' => $game->day_number,
                'voter_id' => $player->id,
            ],
            ['target_id' => $target?->id]
        );

        $this->autoResolveDayIfReady($game->fresh(['players']));

        return redirect()->route('games.show', $game->code);
    }

    public function resolveNight(string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || ! $player->is_host || $game->status !== 'night') {
            return back();
        }

        $this->resolveNightLogic($game->fresh(['players']));

        return redirect()->route('games.show', $game->code);
    }

    public function resolveDay(string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || ! $player->is_host || $game->status !== 'day') {
            return back();
        }

        $this->resolveDayLogic($game->fresh(['players']));

        return redirect()->route('games.show', $game->code);
    }

    public function reset(string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if (! $player || ! $player->is_host || $game->status !== 'ended') {
            return back();
        }

        $game->nightActions()->delete();
        $game->votes()->delete();
        $game->logs()->delete();

        $game->players()->update([
            'role' => null,
            'is_alive' => true,
            'death_reason' => null,
        ]);

        $game->update([
            'status' => 'lobby',
            'day_number' => 0,
            'night_number' => 0,
            'winner' => null,
        ]);

        return redirect()->route('games.show', $game->code);
    }

    public function leave(string $code): RedirectResponse
    {
        $game = $this->findGame($code);
        $player = $this->currentPlayer($game);

        if ($player) {
            session()->forget("player_token.{$game->code}");
            if ($game->status === 'lobby') {
                $player->delete();
            }
        }

        return redirect()->route('home');
    }

    // ---- helpers -------------------------------------------------------

    protected function findGame(string $code): Game
    {
        return Game::where('code', strtoupper($code))->firstOrFail();
    }

    protected function currentPlayer(Game $game): ?Player
    {
        $token = session("player_token.{$game->code}");

        if (! $token) {
            return null;
        }

        return $game->players()->where('token', $token)->first();
    }

    protected function nightViewData(Game $game, Player $player): array
    {
        $myAction = null;

        if ($player->is_alive && in_array($player->role, ['werewolf', 'doctor', 'seer'])) {
            $actionMap = ['werewolf' => 'kill', 'doctor' => 'save', 'seer' => 'inspect'];
            $myAction = $game->nightActions()
                ->where('night_number', $game->night_number)
                ->where('actor_id', $player->id)
                ->where('action', $actionMap[$player->role])
                ->first();
        }

        $wolvesActed = $game->aliveWerewolves()->filter(fn ($w) => $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $w->id)->where('action', 'kill')->exists())->count();

        $doctorAlive = $game->alivePlayers()->firstWhere('role', 'doctor');
        $doctorActed = $doctorAlive && $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $doctorAlive->id)->where('action', 'save')->exists();

        $seerAlive = $game->alivePlayers()->firstWhere('role', 'seer');
        $seerActed = $seerAlive && $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $seerAlive->id)->where('action', 'inspect')->exists();

        return [
            'game' => $game,
            'player' => $player,
            'myAction' => $myAction,
            'wolvesTotal' => $game->aliveWerewolves()->count(),
            'wolvesActed' => $wolvesActed,
            'doctorAlive' => (bool) $doctorAlive,
            'doctorActed' => (bool) $doctorActed,
            'seerAlive' => (bool) $seerAlive,
            'seerActed' => (bool) $seerActed,
            'logs' => $game->publicLogsFor($player),
        ];
    }

    protected function dayViewData(Game $game, Player $player): array
    {
        $myVote = $game->votes()
            ->where('day_number', $game->day_number)
            ->where('voter_id', $player->id)
            ->first();

        $votesIn = $game->votes()->where('day_number', $game->day_number)->count();

        return [
            'game' => $game,
            'player' => $player,
            'myVote' => $myVote,
            'votesIn' => $votesIn,
            'aliveCount' => $game->alivePlayers()->count(),
            'logs' => $game->publicLogsFor($player),
        ];
    }

    protected function autoResolveNightIfReady(Game $game): void
    {
        $wolves = $game->aliveWerewolves();
        $wolvesReady = $wolves->every(fn ($w) => $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $w->id)->where('action', 'kill')->exists());

        $doctor = $game->alivePlayers()->firstWhere('role', 'doctor');
        $doctorReady = ! $doctor || $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $doctor->id)->where('action', 'save')->exists();

        $seer = $game->alivePlayers()->firstWhere('role', 'seer');
        $seerReady = ! $seer || $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('actor_id', $seer->id)->where('action', 'inspect')->exists();

        if ($wolves->isNotEmpty() && $wolvesReady && $doctorReady && $seerReady) {
            $this->resolveNightLogic($game);
        }
    }

    protected function autoResolveDayIfReady(Game $game): void
    {
        $alive = $game->alivePlayers();
        $votesIn = $game->votes()->where('day_number', $game->day_number)->count();

        if ($alive->count() > 0 && $votesIn >= $alive->count()) {
            $this->resolveDayLogic($game);
        }
    }

    protected function resolveNightLogic(Game $game): void
    {
        $killVotes = $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('action', 'kill')
            ->get()
            ->groupBy('target_id');

        $victimId = null;
        if ($killVotes->isNotEmpty()) {
            $max = $killVotes->map->count()->max();
            $topTargets = $killVotes->filter(fn ($v) => $v->count() === $max)->keys();
            $victimId = $topTargets->random();
        }

        $saveAction = $game->nightActions()
            ->where('night_number', $game->night_number)
            ->where('action', 'save')
            ->first();

        $victim = $victimId ? $game->players->firstWhere('id', $victimId) : null;
        $saved = $victim && $saveAction && (int) $saveAction->target_id === (int) $victim->id;

        if (! $victim) {
            $game->addLog('🌙 A strangely quiet night passed. No one was attacked.');
        } elseif ($saved) {
            $game->addLog("💉 The werewolves attacked {$victim->name} in the night, but the doctor saved them just in time!");
        } else {
            $victim->update(['is_alive' => false, 'death_reason' => 'Killed by the werewolves during the night']);
            $game->addLog("🐺 {$victim->name} was found dead this morning. They were a {$this->label($victim->role)}.");
        }

        $game->refresh();
        $game->load('players');

        $this->concludePhase($game, fromNight: true);
    }

    protected function resolveDayLogic(Game $game): void
    {
        $votes = $game->votes()
            ->where('day_number', $game->day_number)
            ->whereNotNull('target_id')
            ->get()
            ->groupBy('target_id');

        $eliminatedId = null;
        if ($votes->isNotEmpty()) {
            $max = $votes->map->count()->max();
            $topTargets = $votes->filter(fn ($v) => $v->count() === $max)->keys();
            if ($topTargets->count() === 1) {
                $eliminatedId = $topTargets->first();
            }
        }

        if ($eliminatedId) {
            $eliminated = $game->players->firstWhere('id', $eliminatedId);
            $eliminated->update(['is_alive' => false, 'death_reason' => 'Lynched by the village']);
            $game->addLog("⚖️ The village voted to lynch {$eliminated->name}. They were a {$this->label($eliminated->role)}!");
        } else {
            $game->addLog('⚖️ The village could not agree on who to lynch. No one was eliminated today.');
        }

        $game->refresh();
        $game->load('players');

        $this->concludePhase($game, fromNight: false);
    }

    protected function concludePhase(Game $game, bool $fromNight): void
    {
        $winner = $this->checkWinCondition($game);

        if ($winner) {
            $game->update(['status' => 'ended', 'winner' => $winner]);
            $reveal = $game->players->map(fn ($p) => "{$p->name} ({$this->label($p->role)})")->implode(', ');
            $game->addLog('🏆 '.($winner === 'werewolves' ? 'The Werewolves have taken over the village!' : 'The Villagers have eliminated every werewolf!'));
            $game->addLog("Final roles: {$reveal}");

            return;
        }

        if ($fromNight) {
            $game->update(['status' => 'day', 'day_number' => $game->day_number + 1]);
            $game->addLog('☀️ The village wakes up and gathers to discuss and vote.');
        } else {
            $game->update(['status' => 'night', 'night_number' => $game->night_number + 1]);
            $game->addLog('🌙 Night falls once again. Those with a role should act now.');
        }
    }

    protected function checkWinCondition(Game $game): ?string
    {
        $wolves = $game->aliveWerewolves()->count();
        $others = $game->aliveNonWerewolves()->count();

        if ($wolves === 0) {
            return 'villagers';
        }

        if ($wolves >= $others) {
            return 'werewolves';
        }

        return null;
    }

    protected function label(?string $role): string
    {
        return Roles::definition($role ?? 'villager')['label'];
    }
}
