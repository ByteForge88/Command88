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

namespace byteforge88\command88\data;

use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class CommandEnum{
    
    public array $values;

    public bool $soft;

    public function __construct(array $values, bool $soft = false){
        $this->values = $values;
        $this->soft = $soft;
        
        if(!$this->soft && $this->values === []){
            throw new \InvalidArgumentException("A hard enum needs at least one choice.");
        }
        
        foreach($this->values as $value){
            if(!is_string($value) || preg_match("/^[a-zA-Z0-9_.:-]+$/D", $value) !== 1){
                throw new \InvalidArgumentException("Enum values must be nonempty identifiers without spaces.");
            }
        }
        
        $this->values = array_values(array_unique($this->values));
    }

    public function toNetwork(string $name) : CommandHardEnum|CommandSoftEnum{
        return $this->soft ? new CommandSoftEnum($name, $this->values) : new CommandHardEnum($name, $this->values);
    }
}