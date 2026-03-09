<?php

namespace TopLeaderboards;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase{

    private LeaderboardManager $manager;

    public function onEnable() : void{

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();

        $this->manager = new LeaderboardManager($this);

        $this->getScheduler()->scheduleRepeatingTask(
            $this->manager,
            $this->getConfig()->get("update-time") * 20
        );
    }

    public function getManager() : LeaderboardManager{
        return $this->manager;
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{

        if(!$sender instanceof Player){
            return true;
        }

        if(!isset($args[0])){
            $sender->sendMessage($this->getConfig()->get("messages")["usage"]);
            return true;
        }

        switch($args[0]){

            case "spawn":

                if(!isset($args[1])) return true;

                $this->manager->spawnLeaderboard($sender,$args[1]);
            break;

            case "remove":

                if(!isset($args[1])) return true;

                $this->manager->removeLeaderboard($args[1]);
                $sender->sendMessage(str_replace("{name}",$args[1],$this->getConfig()->get("messages")["removed"]));
            break;

            case "list":

                $list = implode(", ",array_keys($this->getConfig()->get("leaderboards")));

                $sender->sendMessage(str_replace("{list}",$list,$this->getConfig()->get("messages")["list"]));
            break;
        }

        return true;
    }
}
