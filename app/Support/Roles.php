<?php

namespace App\Support;

use App\Models\Player;
use Illuminate\Support\Collection;

class Roles
{
    public const DEFINITIONS = [
        'werewolf' => [
            'label' => 'Werewolf',
            'team' => 'werewolves',
            'icon' => '🐺',
            'description' => 'Each night, you and your fellow werewolves secretly agree on one player to eliminate. '
                .'By day, blend in with the villagers, lie convincingly, and steer the vote away from yourselves. '
                .'The werewolves win once they equal or outnumber the remaining villagers.',
        ],
        'doctor' => [
            'label' => 'Doctor',
            'team' => 'villagers',
            'icon' => '💉',
            'description' => 'Each night, choose one player (you may choose yourself) to protect. '
                .'If the werewolves attack that player, they survive the night. '
                .'You win alongside the villagers by helping eliminate every werewolf.',
        ],
        'seer' => [
            'label' => 'Seer',
            'team' => 'villagers',
            'icon' => '🔮',
            'description' => 'Each night, choose one player to secretly investigate. '
                .'You will privately learn whether they are a Werewolf or not. '
                .'Use your knowledge carefully during the day without revealing yourself too soon. '
                .'You win alongside the villagers by helping eliminate every werewolf.',
        ],
        'villager' => [
            'label' => 'Villager',
            'team' => 'villagers',
            'icon' => '👤',
            'description' => 'You have no special power at night. '
                .'By day, listen closely, share suspicions, and vote to eliminate whoever you believe is a werewolf. '
                .'You win alongside the village by eliminating every werewolf.',
        ],
    ];

    public static function definition(string $role): array
    {
        return self::DEFINITIONS[$role] ?? self::DEFINITIONS['villager'];
    }

    /**
     * Decide how many of each role a game of $count players should have.
     */
    public static function countsFor(int $count): array
    {
        $werewolves = max(1, intdiv($count, 4));
        $doctor = $count >= 3 ? 1 : 0;
        $seer = $count >= 5 ? 1 : 0;
        $villagers = max(0, $count - $werewolves - $doctor - $seer);

        return compact('werewolves', 'doctor', 'seer', 'villagers');
    }

    /**
     * Shuffle and assign roles to a collection of players, saving each one.
     */
    public static function assign(Collection $players): void
    {
        $counts = self::countsFor($players->count());

        $pool = [];
        for ($i = 0; $i < $counts['werewolves']; $i++) {
            $pool[] = 'werewolf';
        }
        for ($i = 0; $i < $counts['doctor']; $i++) {
            $pool[] = 'doctor';
        }
        for ($i = 0; $i < $counts['seer']; $i++) {
            $pool[] = 'seer';
        }
        for ($i = 0; $i < $counts['villagers']; $i++) {
            $pool[] = 'villager';
        }

        shuffle($pool);

        $players->values()->each(function (Player $player, int $index) use ($pool) {
            $player->update([
                'role' => $pool[$index] ?? 'villager',
                'is_alive' => true,
                'death_reason' => null,
            ]);
        });
    }
}
