<?php

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
        parent::__construct($name, [&true_value, $false_value], $optional);
    }

    protected function networkTypeName() : string{ return "bool"; }

    public function parse(string $value, CommandSender $sender) : bool{
        return parent::parse($value, $sender) === $this->true_value;
    }
}