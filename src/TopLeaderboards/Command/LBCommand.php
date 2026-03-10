<?php

namespace TopLeaderboards\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use TopLeaderboards\Main;

class LBCommand extends Command{

    private Main $plugin;

    public function __construct(Main $plugin){

        parent::__construct("lb","Leaderboard commands");

        $this->setPermission("topleaderboards.admin");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender,string $label,array $args): bool{

        if(!$sender instanceof Player) return true;

        if(!isset($args[0])){

            $sender->sendMessage("/lb spawn <name> | /lb remove <name> | /lb list");

            return true;
        }

        switch($args[0]){

            case "spawn":

                if(!isset($args[1])) return true;

                $this->plugin->getManager()->spawn($args[1],$sender->getPosition());

                $sender->sendMessage("Leaderboard created");
            break;

            case "remove":

                if(!isset($args[1])) return true;

                $this->plugin->getManager()->remove($args[1]);

                $sender->sendMessage("Leaderboard removed");
            break;

            case "list":

                $sender->sendMessage(
                    implode(", ",
                        array_keys($this->plugin->getConfig()->get("leaderboards"))
                    )
                );
            break;
        }

        return true;
    }
}
