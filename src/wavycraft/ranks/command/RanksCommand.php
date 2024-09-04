<?php

declare(strict_types=1);

namespace wavycraft\ranks\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use wavycraft\ranks\utils\RanksManager;
use wavycraft\ranks\Ranks;

class RanksCommand extends Command {

    public function __construct() {
        parent::__construct("rank");
        $this->setLabel("rank");
        $this->setDescription("Set or Remove a players rank");
        $this->setAliases(["r", "ranks"]);
        $this->setPermission("ranks.cmd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage("Usage: /ranks <set|remove|list> <player> [rank]");
            return false;
        }

        $subCommand = strtolower($args[0]);
        $ranksManager = RanksManager::getInstance();

        switch ($subCommand) {
            case "set":
                if (count($args) < 3) {
                    $sender->sendMessage("Usage: /ranks set <player> <rank>");
                    return false;
                }

                $targetName = $args[1];
                $rank = $args[2];
                $target = Ranks::getInstance()->getServer()->getPlayerByPrefix($targetName);

                if ($target === null) {
                    $sender->sendMessage("Player not found...");
                    return false;
                }

                if (!$ranksManager->rankExists($rank)) {
                    $sender->sendMessage("The rank $rank does not exist...");
                    return false;
                }

                $ranksManager->setRank($target, $rank);
                $sender->sendMessage("Set the rank of " . $target->getName() . " to " . $rank . "!");
                break;

            case "remove":
                if (count($args) < 2) {
                    $sender->sendMessage("Usage: /ranks remove <player>");
                    return false;
                }

                $targetName = $args[1];
                $target = Ranks::getInstance()->getServer()->getPlayerByPrefix($targetName);

                if ($target === null) {
                    $sender->sendMessage("Player not found...");
                    return false;
                }

                $ranksManager->removeRank($target);
                $sender->sendMessage("Removed the rank of " . $target->getName() . "!");
                break;

            case "list":
                $ranks = $ranksManager->rankHierarchy();
                if (empty($ranks)) {
                    $sender->sendMessage("No ranks found...");
                    return false;
                }

                $rankList = implode(", ", $ranks);
                $sender->sendMessage("Available ranks: " . $rankList);
                break;

            default:
                $sender->sendMessage("Usage: /ranks <set|remove|list> <player> [rank]");
                return false;
        }
        return true;
    }
}
