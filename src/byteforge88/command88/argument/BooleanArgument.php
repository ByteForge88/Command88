<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use byteforge88\command88\data\CommandEnum;

class BooleanArgument extends EnumArgument{
    
    public function __construct(string $name, array|CommandEnum $toggle = ["true", "false"], bool $optional = false){
        parent::__construct($name, $toggle, $optional);
    }
    
    protected function networkTypeName() : string{ return "bool"; }
    
    public function parse(string $value, CommandSender $sender) : mixed{ return parent::parse($value, $sender) === "true"; }
    
}