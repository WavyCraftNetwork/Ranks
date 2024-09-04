<?php

declare(strict_types=1);

namespace wavycraft\ranks\scorehud;

use pocketmine\player\Player;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\ScoreHud;
use Ifera\ScoreHud\event\TagsResolveEvent;
use wavycraft\ranks\utils\RanksManager;
use wavycraft\ranks\Ranks;

class RankScoreHud {

    public function updateScoreHudTags(Player $player) {
        if (class_exists(ScoreHud::class)) {
            $rank = RanksManager::getInstance()->getPlayerRankDisplay($player);

            $ev = new PlayerTagsUpdateEvent(
                $player,
                [
                    new ScoreTag("ranks.rank", $rank),
                ]
            );
            $ev->call();
        }
    }

    public function onTagResolve(TagsResolveEvent $event) {
        $player = $event->getPlayer();
        $tag = $event->getTag();

        $rank = RanksManager::getInstance()->getPlayerRankDisplay($player);
        
        match ($tag->getName()) {
            "ranks.rank" => $tag->setValue($rank),
            default => null,
        };
    }
}