<?php

namespace TopLeaderboards;

use pocketmine\world\Position;
use pocketmine\world\World;

class LeaderboardManager{

    private Main $plugin;
    private array $boards = [];

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function loadBoards(): void{

        $data = yaml_parse_file($this->plugin->getDataFolder()."leaderboards.yml");

        foreach($data["boards"] ?? [] as $name => $pos){

            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($pos["world"]);

            if($world instanceof World){

                $this->boards[$name] = new Position(
                    $pos["x"],
                    $pos["y"],
                    $pos["z"],
                    $world
                );
            }
        }
    }

    public function spawn(string $name, Position $pos): void{

        $this->boards[$name] = $pos;

        $data = yaml_parse_file($this->plugin->getDataFolder()."leaderboards.yml");

        $data["boards"][$name] = [
            "x"=>$pos->getX(),
            "y"=>$pos->getY(),
            "z"=>$pos->getZ(),
            "world"=>$pos->getWorld()->getFolderName()
        ];

        yaml_emit_file($this->plugin->getDataFolder()."leaderboards.yml",$data);
    }

    public function remove(string $name): void{

        unset($this->boards[$name]);

        $data = yaml_parse_file($this->plugin->getDataFolder()."leaderboards.yml");

        unset($data["boards"][$name]);

        yaml_emit_file($this->plugin->getDataFolder()."leaderboards.yml",$data);
    }

    public function update(): void{

        foreach($this->boards as $name => $pos){

            $type = $this->plugin->getConfig()->get("leaderboards")[$name] ?? null;

            if($type === null) continue;

            $stats = $this->plugin->getStats()["players"] ?? [];

            $values = [];

            foreach($stats as $player => $data){
                $values[$player] = $data[$type] ?? 0;
            }

            arsort($values);

            $top = array_slice($values,0,10,true);

            $text = "§6$name\n";

            $rank = 1;

            foreach($top as $player=>$value){
                $text .= "§e#$rank §f$player §7- §a$value\n";
                $rank++;
            }

            foreach($pos->getWorld()->getPlayers() as $player){

                if($player->getPosition()->distance($pos) <= 15){
                    $player->sendPopup($text);
                }
            }
        }
    }
}
