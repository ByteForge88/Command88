<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

class BooleanArgument extends EnumArgument {
    
    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, ['true', 'false'], $optional);
    }
    
    protected function networkTypeName() : string{ return 'bool'; }
    
    public function parse(string $value, CommandSender $sender) : mixed{ return parent::parse($value, $sender) === 'true'; }
    
}