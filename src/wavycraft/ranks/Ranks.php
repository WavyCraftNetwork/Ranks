<?php

declare(strict_types=1);

namespace wavycraft\ranks;

use pocketmine\plugin\PluginBase;

use wavycraft\ranks\utils\RanksManager;
use wavycraft\ranks\command\RanksCommand;
use wavycraft\ranks\event\RankListener;

class Ranks extends PluginBase {

    private static $instance;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->saveResource("ranks.yml");
        $this->getServer()->getCommandMap()->register("Ranks", new RanksCommand());
        $this->getServer()->getPluginManager()->registerEvents(new RankListener(), $this);
    }

    protected function onDisable() : void{
        RanksManager::getInstance()->saveRanks();
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}