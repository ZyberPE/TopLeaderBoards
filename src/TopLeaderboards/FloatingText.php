<?php

namespace TopLeaderboards;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;

class FloatingText extends Entity{

    private string $text;

    public function __construct(Location $location,string $text){
        parent::__construct($location);
        $this->text = $text;

        $this->setNameTag($text);
        $this->setNameTagAlwaysVisible();
        $this->setNameTagVisible();
        $this->setScale(0.01);
    }

    public function setText(string $text){

        $this->text = $text;
        $this->setNameTag($text);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo{
        return new EntitySizeInfo(0.01,0.01);
    }

    public static function getNetworkTypeId(): string{
        return "minecraft:armor_stand";
    }

    public function onUpdate(int $tick): bool{
        return true;
    }
}
