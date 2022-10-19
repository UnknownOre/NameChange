<?php
declare(strict_types=1);

namespace UnknownOre\NameChange\event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class PlayerNameChangeEvent extends Event{

	public function __construct(private Player $player, private string $oldName){

	}

	public function getPlayer():Player{
		return $this->player;
	}

	public function getOldName():string{
		return $this->oldName;
	}
}