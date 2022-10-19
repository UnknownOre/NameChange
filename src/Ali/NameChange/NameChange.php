<?php
declare(strict_types=1);

namespace Ali\NameChange;

use Ali\NameChange\event\PlayerNameChangeEvent;
use Ali\NameChange\utils\PlayerUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use function is_file;
use function strtolower;
use function yaml_emit_file;
use function yaml_parse_file;

class NameChange extends PluginBase implements  Listener{

	private array $names = [];

	protected function onEnable():void{
		$path = $this->getDataFolder()."names.yml";
		is_file($path) && $this->names = yaml_parse_file($path);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onJoin(PlayerJoinEvent $event): void{
		$player = $event->getPlayer();
		$id = $player->getUniqueId()->toString();
		$name = strtolower($player->getName());

		if(isset($this->names[$id])){
			$oldName = $this->names[$id];

			if($oldName !== $name) {
				(new PlayerNameChangeEvent($event->getPlayer(), $oldName))->call();
				$this->names[$id] = $name;
			}
			return;
		}

		$this->names[$id] = $name;
	}

	public function onNameChange(PlayerNameChangeEvent $event): void{
		$player = $event->getPlayer();
		$oldName = $event->getOldName();

		$oldInventory = PlayerUtils::getOfflinePlayerInventory($oldName);

		$player->getInventory()->setContents($oldInventory["inventory"]);
		$player->getArmorInventory()->setContents($oldInventory["armor"]);
		$player->getEnderInventory()->setContents($oldInventory["enderChest"]);
		$player->getOffHandInventory()->setContents($oldInventory["offHand"]);

		PlayerUtils::deletePlayerData($oldName);
	}

	public function onDisable():void{
		yaml_emit_file($this->getDataFolder() . "names.yml", $this->names);
	}

}