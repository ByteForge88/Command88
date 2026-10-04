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

class CustomArgument extends BaseArgument{

    public string $name;

    private \Closure $parser;
    
    private int $packetType;

    public bool $optional;
     
    public function __construct(
        string $name,
        \Closure $parser,
        int $packetType = Packet::ARG_TYPE_STRING,
        bool $optional = false
    ){
        parent::__construct($name, $optional);
        $this->name = $name;
        $this->parser = $parser;
        $this->packetType = $packetType;
        $this->optional = $optional;
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{ return ($this->parser)($value, $sender); }
    
    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, $this->packetType, 0, $this->optional);
    }
}