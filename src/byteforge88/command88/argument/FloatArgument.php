<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\Messages;

class FloatArgument extends BaseArgument {

    public function __construct(private string $name, private bool $optional = false) {
        parent::__construct($name, $optional);
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{
        if (!is_numeric($value) || !is_finite($number = (float) $value)) {
            throw new ArgumentException(Messages::get('invalid-float', ['argument' => $this->name]));
        }
        
        return $number;
    }
    
    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, Packet::ARG_TYPE_FLOAT, 0, $this->optional);
    }
}