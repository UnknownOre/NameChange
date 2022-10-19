<?php
declare(strict_types=1);

namespace UnknownOre\NameChange\utils;

use JetBrains\PhpStorm\ArrayShape;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use Webmozart\PathUtil\Path;
use function is_file;
use function is_null;
use function strtolower;
use function unlink;

class PlayerUtils{

	#[ArrayShape([
		"armor" => "array",
		"inventory" => "array",
		"offHand" => "array",
		"enderChest" => "array"
	])] public static function getOfflinePlayerInventory(string $name): ?array{
		$data = Server::getInstance()->getOfflinePlayerData($name);

		$result = [
			"armor" => [],
			"inventory" => [],
			"offHand" => [],
			"enderChest" => []
		];
		if(is_null($data)) {
			return $result;
		}

		$inventoryTag = $data->getListTag("Inventory");
		if($inventoryTag !== null) {
			$inventoryItems = [];
			$armorInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($inventoryTag as $item) {
				$slot = $item->getByte("Slot");
				if($slot >= 0 && $slot < 9) {
				}elseif($slot >= 100 && $slot < 104){
					$armorInventoryItems[$slot - 100] = Item::nbtDeserialize($item);
				}elseif($slot >= 9 && $slot < 36 + 9){
					$inventoryItems[$slot - 9] = Item::nbtDeserialize($item);
				}
			}

			$result["armor"] = $armorInventoryItems;
			$result["inventory"] = $inventoryItems;
		}
		$offHand = $data->getCompoundTag("OffHandItem");


		if($offHand !== null) {
			$result["offHand"] = [Item::nbtDeserialize($offHand)];
		}

		$enderChestInventoryTag = $data->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null) {
			$enderChestInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $item) {
				$enderChestInventoryItems[$item->getByte("Slot")] = Item::nbtDeserialize($item);
			}
			$result["enderChest"] = $enderChestInventoryItems;
		}

		return $result;
	}

	public static function deletePlayerData(string $name): void{
		$path = Path::join(Server::getInstance()->getDataPath(), 'players', strtolower($name) . '.dat');

		if(is_file($path)) {
			unlink($path);
		}
	}


}