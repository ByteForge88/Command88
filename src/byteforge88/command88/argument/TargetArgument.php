<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use pocketmine\player\Player;

use pocketmine\entity\Entity;

use byteforge88\command88\Messages;

/* use PlayerArgument::class instead if you need the player only... */
class TargetArgument extends BaseArgument {
    
    public function __construct(string $name = 'target', bool $optional = false) {
        parent::__construct($name, $optional);
    }

    public function parse(string $value, CommandSender $sender) : mixed{
        if (!str_starts_with($value, '@')) {
            $player = $sender->getServer()->getPlayerExact($value);
            $targets = $player === null ? [] : [$player];
        } else {
            $players = array_values($sender->getServer()->getOnlinePlayers());
            
            switch ($value) {
                case '@s':
                    if (!$sender instanceof Entity) {
                        $this->fail('selector-player-only');
                    }
                
                    $targets = [$sender];
                    break;
                
                case '@a':
                    $targets = $players;
                    break;
                
                case '@r':
                    $targets = $players === [] ? [] : [$players[array_rand($players)]];
                    break;
                
                case '@p':
                    if (!$sender instanceof Player) {
                        $this->fail('selector-player-only');
                    }
                
                    $players = array_values(array_filter($players, fn(Player $p) => $p->getWorld() === $sender->getWorld()));
                    $position = $sender->getPosition();
                    usort($players, fn(Player $a, Player $b) => $a->getPosition()->distanceSquared($position) <=> $b->getPosition()->distanceSquared($position));
                    $targets = array_slice($players, 0, 1);
                    break;
                
                case '@e':
                    $worlds = $sender instanceof Entity ? [$sender->getWorld()] : $sender->getServer()->getWorldManager()->getWorlds();
                    $targets = [];
                
                    foreach ($worlds as $world) {
                        foreach ($world->getEntities() as $entity) {
                            if (!$entity->isClosed() && $entity->isAlive()) {
                                $targets[] = $entity;
                            }
                        }
                    }
                    break;
                
                default:
                    $this->fail('invalid-selector');
            }
        }
        
        if ($targets === []) {
            $this->fail('target-not-found');
        }
        
        return $targets;
    }

    private function fail(string $message) : never{
        throw new ArgumentException(Messages::get($message, ['argument' => $this->name]));
    }

    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, Packet::ARG_TYPE_TARGET, 0, $this->optional);
    }
}
