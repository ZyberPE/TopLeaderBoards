<?php

namespace TopLeaderboards\Command;

use TopLeaderboards\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class LeaderboardCommand extends Command{

    private Main $plugin;

    public function __construct(Main $plugin){
        parent::__construct("lb","Leaderboard commands","/lb");
        $this->setPermission("topleaderboards.command");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender,string $label,array $args): bool{

        if(!$sender instanceof Player){
            return false;
        }

        if(!isset($args[0])){
            return false;
        }

        switch($args[0]){

            case "spawn":

                $name = $args[1] ?? "";
                $this->plugin->getManager()->spawnBoard($name,$sender->getPosition());
                $sender->sendMessage("Leaderboard $name spawned");
            break;

            case "remove":

                $name = $args[1] ?? "";
                $this->plugin->getManager()->removeBoard($name);
                $sender->sendMessage("Leaderboard removed");
            break;

            case "list":

                $list = implode(", ",array_keys(
                    $this->plugin->getConfig()->get("leaderboards")
                ));
                $sender->sendMessage("Leaderboards: ".$list);
            break;
        }

        return true;
    }
}
