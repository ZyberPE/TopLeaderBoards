<?php

namespace TopLeaderboards;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{

    private LeaderboardManager $manager;
    private array $stats = [];

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->saveResource("stats.yml");

        $this->stats = yaml_parse_file($this->getDataFolder() . "stats.yml");

        $this->manager = new LeaderboardManager($this);

        $this->getServer()->getPluginManager()->registerEvents($this,$this);

        $this->getScheduler()->scheduleRepeatingTask(
            new LeaderboardTask($this->manager),
            $this->getConfig()->get("update-time") * 20
        );

        $this->getServer()->getCommandMap()->register("lb",
            new Command\LeaderboardCommand($this)
        );
    }

    public function getManager(): LeaderboardManager{
        return $this->manager;
    }

    public function getStats(): array{
        return $this->stats;
    }

    public function saveStats(): void{
        yaml_emit_file($this->getDataFolder()."stats.yml",$this->stats);
    }

    private function initPlayer(Player $player): void{
        $name = strtolower($player->getName());
        if(!isset($this->stats["players"][$name])){
            $this->stats["players"][$name] = [
                "kills" => 0,
                "deaths" => 0,
                "mined" => 0
            ];
        }
    }

    public function onDeath(PlayerDeathEvent $event): void{
        $player = $event->getPlayer();
        $this->initPlayer($player);

        $this->stats["players"][strtolower($player->getName())]["deaths"]++;

        $killer = $player->getLastDamageCause()?->getEntity();
        if($killer instanceof Player){
            $this->initPlayer($killer);
            $this->stats["players"][strtolower($killer->getName())]["kills"]++;
        }
    }

    public function onBreak(BlockBreakEvent $event): void{
        $player = $event->getPlayer();
        $this->initPlayer($player);

        $this->stats["players"][strtolower($player->getName())]["mined"]++;
    }

    public function onDisable(): void{
        $this->saveStats();
    }
}
