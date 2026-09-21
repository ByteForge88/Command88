<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use byteforge88\command88\data\CommandEnum;

class SoftEnumArgument extends EnumArgument {
    
    public function __construct(string $name, array $values, bool $optional = false){
        parent::__construct($name, new CommandEnum($values, soft: true), $optional);
    }
}
