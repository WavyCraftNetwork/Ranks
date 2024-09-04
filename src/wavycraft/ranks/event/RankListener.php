<?php

declare(strict_types=1);

namespace wavycraft\ranks\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\player\chat\LegacyRawChatFormatter;

use wavycraft\ranks\utils\RanksManager;
use wavycraft\ranks\scorehud\RankScoreHud;

class RankListener implements Listener {

    public function onJoin(PlayerJoinEvent $event) {
        $scoreHud = new RankScoreHud();
        $player = $event->getPlayer();
        $ranksManager = RanksManager::getInstance();
        $ranksManager->createPlayerProfile($player);
        $ranksManager->assignPermissions($player);
        $ranksManager->updatePlayerDisplayName($player);
        $scoreHud->updateScoreHudTags($player);
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $ranksManager = RanksManager::getInstance();
        $ranksManager->saveRanks();
        $ranksManager->removePermissions($player);
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $rank = RanksManager::getInstance()->getRank($player);
        $rankChatFormat = $rank ? RanksManager::getInstance()->getChatFormat($rank) : "{playerName}: {message}";
        $formattedMessage = str_replace(["{playerName}", "{message}"], [$player->getName(), $event->getMessage()], $rankChatFormat);
        $event->setFormatter(new LegacyRawChatFormatter($formattedMessage));
    }

    public function onPlayerTeleport(EntityTeleportEvent $event) {
        $player = $event->getEntity();
        $scoreHud = new RankScoreHud();
        $scoreHud->updateScoreHudTags($player);
    }
}
