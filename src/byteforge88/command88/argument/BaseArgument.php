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

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

abstract class BaseArgument{

    public string $name;

    public bool $optional;
    
    public function __construct(string $name, bool $optional = false){
        $this->name = $name;
        $this->optional = $optional;
        
        if(preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/D", $this->name) !== 1){
            throw new \InvalidArgumentException("Invalid argument name: " . $this->name);
        }
    }
    
    final public function getName() : string{ return $this->name; }
    
    final public function isOptional() : bool{ return $this->optional; }
    
    public function isGreedy() : bool{ return false; }
    
    public function canParse(string $value, CommandSender $sender) : bool{
        try{
            $this->parse($value, $sender);
            
            return true;
        }catch(ArgumentException){
            return false;
        }
    }
    
    abstract public function parse(string $value, CommandSender $sender) : mixed;
    
    abstract public function toNetwork(string $scope, array $playerNames = []) : CommandParameter;
    
}