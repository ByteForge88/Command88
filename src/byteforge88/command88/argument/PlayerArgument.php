<?php

/**
 *
 *       ____                                          _  ___   ___  
 *      / ___|___  _ __ ___  _ __ ___   __ _ _ __   __| |( _ ) ( _ ) 
 *     | |   / _ \| '_ ` _ \| '_ ` _ \ / _` | '_ \ / _` |/ _ \ / _ \ 
 *     | |__| (_) | | | | | | | | | | | (_| | | | | (_| | (_) | (_) |
 *      \____\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|\__,_|\___/ \___/ 
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ByteForge88
 *
 **/

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use byteforge88\command88\Messages;

class PlayerArgument extends TargetArgument{

    public string $name;
    
    public function __construct(string $name = "player", bool $optional = false){
        parent::__construct($name, $optional);
        $this->name = $name;
    }

    public function parse(string $value, CommandSender $sender) : mixed{
        $players = array_values(array_filter(parent::parse($value, $sender), fn($entity) => $entity instanceof Player));
        
        if(count($players) !== 1){
            throw new ArgumentException(Messages::get("player-not-found", ["argument" => $this->name]));
        }
        
        return $players[0];
    }
}