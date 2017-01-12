<?php

namespace HittmanA\factionspp;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class MainClass extends PluginBase implements Listener {

	/** @var Config */
	protected $fac;
	//Faction name tag coming soon >:D
	//$player->setDisplayName("My Rank" . $player->getName());
	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->facs = new Config($this->getDataFolder() . "factions.json", Config::JSON, []);
		$this->playerInfo = new Config($this->getDataFolder() . "players.json", Config::JSON, []);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getPluginManager()->registerEvents(new Events($this), $this);
		$this->getLogger()->info(TextFormat::YELLOW . "Loaded!");
	}

	public function onDisable() {
		$this->getLogger()->info(TextFormat::YELLOW . "Unloading!");
	}

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$dispName = $player->getName();
		$fac = $this->playerInfo->$dispName->faction;
		if(isset($fac)) {
			$prefix = "[$fac]";
			$player->setDisplayName($prefix . " " . $player->getName());
			$player->setNameTag($prefix . " " . $player->getName());
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$displayName = $sender->getName();
		$subcmd = strtolower(array_shift($args));
		switch ($command->getName()) {
			case "factionspp":
			case "fpp":
			case "f":
				if($sender instanceof Player) {
						if($subcmd === "create") {
							if(isset($args[0])) {
								if(isset($this->playerInfo->$displayName["faction"])) {
									$sender->sendMessage(TextFormat::RED . "You are already in a faction!");
								}else{
									$facName = array_shift($args);
									$this->facs->set($facName, [
										"name" => strtolower($facName),
										"display" => $facName,
										"leader" => $displayName,
										"officers" => [],
										"members" => []
									]);
									$this->playerInfo->set($displayName,[
										"name" => $displayName,
										"faction" => $facName,
										"role" => "Leader"
									]);
									$this->facs->save(true);
									$this->playerInfo->save(true);
									$sender->sendMessage(TextFormat::GREEN . "Faction created!");
								}
							} else {
								$sender->sendMessage(TextFormat::GOLD . "Usage: /factionspp create <name>");
							}
						}elseif ($subcmd === "info") {
							if(isset($this->playerInfo->$displayName)) {
								$playerFPPProfile = $this->playerInfo->$displayName;
								$playerFac = $playerFPPProfile["faction"];
								$playerFacInfo = $this->facs->$playerFac;
								$sender->sendMessage(TextFormat::GOLD . "Faction: " . $playerFac);
								$sender->sendMessage(TextFormat::GREEN . "Your Role: " . $playerFPPProfile["role"]);
							}else{
								$sender->sendMessage(TextFormat::RED . "You must be part of a faction to run this command!");
							}
						}
				} else {
					$sender->sendMessage(TextFormat::RED . "Please run this command in-game");
				}
				return true;
			default:
				return false;
		}
	}

}
