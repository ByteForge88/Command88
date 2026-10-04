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

use byteforge88\command88\data\CommandEnum;

use byteforge88\command88\Messages;

class EnumArgument extends BaseArgument{

    public string $name;

    public CommandEnum $enum;

    public bool $optional;
    
    public function __construct(string $name, array|CommandEnum $values, bool $optional = false){
        parent::__construct($name, $optional);
        $this->name = $name;
        $this->enum = is_array($values) ? new CommandEnum($values) : $values;
        $this->optional = $optional;
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{
        if(!$this->enum->soft && !in_array($value, $this->enum->values, true)){
            throw new ArgumentException(Messages::get("invalid-choice", ["argument" => $this->name, "choices" => implode(", ", $this->enum->values)]));
        }
        
        return $value;
    }
    
    protected function networkTypeName() : string{ return "string"; }

    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        $enum = $this->enum->toNetwork($this->enum->soft ? $scope . "_" . $this->name : $this->networkTypeName());
        
        return $this->enum->soft
            ? CommandParameter::softEnum($this->name, $enum, 0, $this->optional)
            : CommandParameter::enum($this->name, $enum, 0, $this->optional);
    }
}