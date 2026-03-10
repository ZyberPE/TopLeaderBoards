<?php

namespace TopLeaderboards;

use pocketmine\player\Player;
use pocketmine\world\Position;

class LeaderboardManager{

    private Main $plugin;
    private array $boards = [];

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function spawnBoard(string $name, Position $pos): void{
        $this->boards[$name] = $pos;
    }

    public function removeBoard(string $name): void{
        unset($this->boards[$name]);
    }

    public function getBoards(): array{
        return $this->boards;
    }

    public function update(): void{
        foreach($this->boards as $name => $pos){
            $this->updateBoard($name,$pos);
        }
    }

    private function updateBoard(string $name, Position $pos): void{

        $config = $this->plugin->getConfig()->get("leaderboards");
        if(!isset($config[$name])) return;

        $type = $config[$name]["type"];

        $stats = $this->plugin->getStats()["players"] ?? [];

        $values = [];

        foreach($stats as $player => $data){
            $values[$player] = $data[$type] ?? 0;
        }

        arsort($values);

        $top = array_slice($values,0,10,true);

        $text = "§6$name\n";

        $rank = 1;
        foreach($top as $player => $value){
            $text .= "§e#$rank §f$player §7- §a$value\n";
            $rank++;
        }

        foreach($pos->getWorld()->getPlayers() as $p){
            $p->sendPopup($text);
        }
    }
}
