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

use byteforge88\command88\Messages;

class IntegerArgument extends BaseArgument{

    public string $name;

    public bool $optional;

    protected ?int $min;

    protected ?int $max;
    
    public function __construct(
        string $name,
        bool $optional = false,
        ?int $min = null,
        ?int $max = null
    ){
        parent::__construct($name, $optional);
        $this->name = $name;
        $this->optional = $optional;
        $this->min = $min;
        $this->max = $max;
        
        if($this->min !== null && $this->max !== null && $this->min > $this->max){
            throw new \InvalidArgumentException("Minimum exceeds maximum.");
        }
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{
        if(preg_match("/^[+-]?[0-9]+$/D", $value) !== 1){
            throw new ArgumentException(Messages::get("invalid-integer", ["argument" => $this->name]));
        }
        
        $negative = str_starts_with($value, "-");
        $digits = ltrim(ltrim($value, "+-"), "0");
        $digits = $digits === "" ? "0" : $digits;
        $limit = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;
        $overflow = strlen($digits) > strlen($limit) || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0);
        $number = (int) $value;
        
        if($overflow || ($this->min !== null && $number < $this->min) || ($this->max !== null && $number > $this->max)){
            throw new ArgumentException(Messages::get("integer-range", ["argument" => $this->name, "min" => $this->min ?? PHP_INT_MIN, "max" => $this->max ?? PHP_INT_MAX]));
        }
        
        return $number;
    }
    
    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, Packet::ARG_TYPE_INT, 0, $this->optional);
    }
}