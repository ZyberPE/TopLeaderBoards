<?php

namespace TopLeaderboards;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\entity\Location;
use onebone\economyapi\EconomyAPI;

class LeaderboardManager extends Task{

    private Main $plugin;
    private array $holograms = [];

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function spawnLeaderboard(Player $player,string $name){

        $cfg = $this->plugin->getConfig()->get("leaderboards");

        if(!isset($cfg[$name])){
            $player->sendMessage($this->plugin->getConfig()->get("messages")["not-found"]);
            return;
        }

        $pos = $player->getPosition();

        $text = $this->buildText($name);

        $entity = new FloatingText(
            Location::fromObject($pos,$player->getWorld()),
            $text
        );

        $entity->spawnToAll();

        $this->holograms[$name] = $entity;

        $player->sendMessage(str_replace("{name}",$name,$this->plugin->getConfig()->get("messages")["spawned"]));
    }

    public function removeLeaderboard(string $name){

        if(isset($this->holograms[$name])){
            $this->holograms[$name]->flagForDespawn();
            unset($this->holograms[$name]);
        }
    }

    public function onRun(): void{

        foreach($this->holograms as $name=>$entity){

            $entity->setText($this->buildText($name));
        }
    }

    private function buildText(string $name): string{

        $cfg = $this->plugin->getConfig()->get("leaderboards")[$name];

        $title = $cfg["title"];
        $lines = $cfg["lines"];
        $format = $cfg["format"];
        $type = $cfg["type"];

        $data = $this->getData($type);

        arsort($data);

        $text = $title."\n";

        $i = 1;

        foreach($data as $player=>$value){

            $text .= str_replace(
                ["{rank}","{player}","{value}"],
                [$i,$player,$value],
                $format
            )."\n";

            if($i++ >= $lines) break;
        }

        return $text;
    }

    private function getData(string $type): array{

        $data = [];

        foreach($this->plugin->getServer()->getOfflinePlayers() as $player){

            switch($type){

                case "kills":
                    $data[$player->getName()] = $player->getPlayer()?->getKills() ?? 0;
                break;

                case "deaths":
                    $data[$player->getName()] = $player->getPlayer()?->getDeaths() ?? 0;
                break;

                case "money":

                    if(class_exists(EconomyAPI::class)){
                        $data[$player->getName()] = EconomyAPI::getInstance()->myMoney($player->getName());
                    }else{
                        $data[$player->getName()] = 0;
                    }

                break;

                default:
                    $data[$player->getName()] = 0;
            }
        }

        return $data;
    }
}
