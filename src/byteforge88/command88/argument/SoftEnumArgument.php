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

use byteforge88\command88\data\CommandEnum;

class SoftEnumArgument extends EnumArgument{
    
    public function __construct(string $name, array $values, bool $optional = false){
        parent::__construct($name, new CommandEnum($values, soft: true), $optional);
    }
}
