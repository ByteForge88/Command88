<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use byteforge88\command88\Messages;

class PlayerArgument extends TargetArgument{
    
    public function __construct(string $name = 'player', bool $optional = false) {
        parent::__construct($name, $optional);
    }

    public function parse(string $value, CommandSender $sender) : mixed{
        $players = array_values(array_filter(parent::parse($value, $sender), fn($entity) => $entity instanceof Player));
        
        if (count($players) !== 1) {
            throw new ArgumentException(Messages::get('player-not-found', ['argument' => $this->name]));
        }
        
        return $players[0];
    }
}