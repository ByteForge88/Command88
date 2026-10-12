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

use byteforge88\command88\data\CommandEnum;

class BooleanArgument extends EnumArgument{

    public string $true_value;

    public function __construct(
        string $name,
        string $true_value = "true",
        string $false_value = "false",
        bool $optional = false
    ){
        parent::__construct($name, [$true_value, $false_value], $optional);
        $this->true_value = $true_value;
    }

    protected function networkTypeName() : string{ return "bool"; }

    public function parse(string $value, CommandSender $sender) : bool{
        return parent::parse($value, $sender) === $this->true_value;
    }
}