<?php

namespace TopLeaderboards;

use pocketmine\scheduler\Task;

class LeaderboardTask extends Task{

    private LeaderboardManager $manager;

    public function __construct(LeaderboardManager $manager){
        $this->manager = $manager;
    }

    public function onRun(): void{
        $this->manager->update();
    }
}
