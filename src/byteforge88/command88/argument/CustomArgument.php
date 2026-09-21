<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\Messages;

class CustomArgument extends BaseArgument {
    
    private int $packetType;
     
    public function __construct(
        private string $name,
        private \Closure $parser,
        private int $packetType = Packet::ARG_TYPE_STRING,
        private bool $optional = false
    ) {
        parent::__construct($name, $optional);
        $this->packetType = $packetType;
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{ return ($this->parser)($value, $sender); }
    
    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, $this->packetType, 0, $this->optional);
    }
}
