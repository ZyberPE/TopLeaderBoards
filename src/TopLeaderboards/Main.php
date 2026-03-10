<?php

namespace TopLeaderboards;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{

    private array $stats = [];
    private LeaderboardManager $manager;

    public function onEnable(): void{

        $this->saveDefaultConfig();
        $this->saveResource("leaderboards.yml");
        $this->saveResource("stats.yml");

        $this->stats = yaml_parse_file($this->getDataFolder()."stats.yml");

        $this->manager = new LeaderboardManager($this);

        $this->getServer()->getPluginManager()->registerEvents($this,$this);

        $this->getCommand("lb")->setExecutor(new LBCommand($this));
        
        $this->getServer()->getCommandMap()->register("lb",
            new LBCommand($this)
        );

        $this->getScheduler()->scheduleRepeatingTask(
            new LeaderboardTask($this->manager),
            $this->getConfig()->get("update-time") * 20
        );

        $this->manager->loadBoards();
    }

    public function getManager(): LeaderboardManager{
        return $this->manager;
    }

    public function addStat(string $player,string $type): void{

        $player = strtolower($player);

        if(!isset($this->stats["players"][$player])){
            $this->stats["players"][$player] = [
                "kills" => 0,
                "deaths" => 0,
                "mined" => 0
            ];
        }

        $this->stats["players"][$player][$type]++;
    }

    public function getStats(): array{
        return $this->stats;
    }

    public function saveStats(): void{
        yaml_emit_file($this->getDataFolder()."stats.yml",$this->stats);
    }

    public function onDeath(PlayerDeathEvent $event): void{

        $player = $event->getPlayer();

        $this->addStat($player->getName(),"deaths");

        $killer = $player->getLastDamageCause()?->getEntity();

        if($killer instanceof Player){
            $this->addStat($killer->getName(),"kills");
        }
    }

    public function onBreak(BlockBreakEvent $event): void{
        $this->addStat($event->getPlayer()->getName(),"mined");
    }

    public function onDisable(): void{
        $this->saveStats();
    }
}
