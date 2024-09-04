<?php

declare(strict_types=1);

namespace wavycraft\ranks\utils;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\permission\PermissionAttachment;

use wavycraft\ranks\Ranks;
use wavycraft\ranks\scorehud\RankScoreHud;

class RanksManager {
    use SingletonTrait;

    private $ranksData;
    private $ranksConfig;
    private $defaultRank;
    private $attachments = [];
    private $customTags = [];

    public function __construct() {
        $this->loadRanks();
        $this->loadRanksConfig();
    }

    public function loadRanks() {
        $this->ranksData = (new Config(Ranks::getInstance()->getDataFolder() . "player_ranks.json", Config::JSON))->getAll();
    }

    public function saveRanks() {
        $config = new Config(Ranks::getInstance()->getDataFolder() . "player_ranks.json", Config::JSON);
        $config->setAll($this->ranksData);
        $config->save();
    }

    public function loadRanksConfig() {
        $this->ranksConfig = (new Config(Ranks::getInstance()->getDataFolder() . "ranks.yml", Config::YAML))->getAll();
        $this->defaultRank = $this->ranksConfig['default_rank'] ?? null;
    }

    public function createTag(string $tag, string $value) {
        $this->customTags[$tag] = $value;
        $this->updateAllPlayerDisplayNames();
    }

    public function replaceTags(string $text) : string{
        foreach ($this->customTags as $tag => $value) {
            $text = str_replace("{" . $tag . "}", $value, $text);
        }
        return $text;
    }

    public function setRank(Player $player, string $rank) {
        if ($this->rankExists($rank)) {
            $scoreHud = new RankScoreHud();
            $this->removePermissions($player);
            $this->ranksData[$player->getName()] = $rank;
            $this->saveRanks();
            $this->assignPermissions($player);
            $this->updatePlayerDisplayName($player);
            $scoreHud->updateScoreHudTags($player);
        } else {
            $player->sendMessage("The rank $rank does not exist.");
        }
    }

    public function getRank(Player $player) : ?string{
        return $this->ranksData[$player->getName()] ?? $this->defaultRank;
    }

    public function getAllRanks() : array{
        $config = (new Config(Ranks::getInstance()->getDataFolder() . "ranks.yml", Config::YAML))->getAll();
        $ranks = [];
        foreach ($config['ranks'] as $rankName => $rankData) {
            $ranks[$rankName] = $rankData['rank_display'];
        }
        return $ranks;
    }

    public function removeRank(Player $player) {
        if (isset($this->ranksData[$player->getName()])) {
            $this->removePermissions($player);
            unset($this->ranksData[$player->getName()]);
            $this->saveRanks();
            $this->assignPermissions($player);
            $this->updatePlayerDisplayName($player);
        }
    }

    public function rankExists(string $rank) : bool{
        return isset($this->ranksConfig['ranks'][$rank]);
    }

    public function rankHierarchy(): array {
        return $this->ranksConfig['hierarchy'] ?? [];
    }

    public function getRankPermissions(string $rank) : ?array{
        return $this->ranksConfig['ranks'][$rank]['permissions'] ?? null;
    }

    public function getDefaultRank() : ?string{
        return $this->defaultRank;
    }

    public function getRankDisplay(string $rank) : ?string{
        return $this->ranksConfig['ranks'][$rank]['rank_display'] ?? $rank;
    }

    public function getPlayerRankDisplay(Player $player) : ?string {
        $rank = $this->getRank($player);
        return $this->getRankDisplay($rank);
    }

    public function getRankTag(string $rank) : ?string{
        return $this->ranksConfig['ranks'][$rank]['rank_player_tag'] ?? null;
    }

    public function getChatFormat(string $rank) : ?string{
        $format = $this->ranksConfig['ranks'][$rank]['rank_chat_format'] ?? null;
        return $format ? $this->replaceTags($format) : null;
    }

    public function setRankTag(string $rank, string $tag) {
        $this->ranksConfig['ranks'][$rank]['rank_player_tag'] = $tag;
    }

    public function setChatFormat(string $rank, string $format) {
        $this->ranksConfig['ranks'][$rank]['rank_chat_format'] = $format;
    }

    public function createPlayerProfile(Player $player) {
        if (!isset($this->ranksData[$player->getName()])) {
            $this->ranksData[$player->getName()] = $this->defaultRank;
            $this->saveRanks();
        }
        $this->assignPermissions($player);
        $this->updatePlayerDisplayName($player);
    }

    public function updatePlayerDisplayName(Player $player) {
        $scoreHud = new RankScoreHud();
        $rank = $this->getRank($player);
        $rankTag = $rank ? $this->getRankTag($rank) : "{playerName}";
        $displayName = str_replace("{playerName}", $player->getName(), $rankTag);
        $displayName = $this->replaceTags($displayName);
        $player->setDisplayName($displayName);
        $scoreHud->updateScoreHudTags($player);
    }

    public function assignPermissions(Player $player) {
        $rank = $this->getRank($player);
        $permissions = $this->getInheritedPermissions($rank);

        if (!empty($permissions)) {
            $attachment = $this->attachments[$player->getName()] ?? $player->addAttachment(Ranks::getInstance());
            $this->attachments[$player->getName()] = $attachment;

            foreach ($permissions as $permission) {
                $attachment->setPermission($permission, true);
            }
        }
    }

    public function removePermissions(Player $player) {
        if (isset($this->attachments[$player->getName()])) {
            $attachment = $this->attachments[$player->getName()];
            $rank = $this->getRank($player);
            $permissions = $this->getInheritedPermissions($rank);
            if ($permissions !== null) {
                foreach ($permissions as $permission) {
                    $attachment->unsetPermission($permission);
                }
            }
            $player->removeAttachment($attachment);
            unset($this->attachments[$player->getName()]);
        }
    }

    private function updateAllPlayerDisplayNames() {
        foreach (Ranks::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $this->updatePlayerDisplayName($player);
        }
    }

    public function getInheritedPermissions(string $rank) : array {
        $visitedRanks = [];
        return $this->gatherPermissions($rank, $visitedRanks);
    }

    private function gatherPermissions(string $rank, array &$visitedRanks) : array {
        if (in_array($rank, $visitedRanks)) {
            return [];
        }

        $visitedRanks[] = $rank;
        $permissions = $this->getRankPermissions($rank) ?? [];
        $rankData = $this->ranksConfig['ranks'][$rank] ?? null;

        if ($rankData && isset($rankData['inheritance'])) {
            foreach ($rankData['inheritance'] as $inheritedRank) {
                $permissions = array_merge($permissions, $this->gatherPermissions($inheritedRank, $visitedRanks));
            }
        }

        return array_unique($permissions);
    }
}
