<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\Messages;

abstract class BaseArgument {
    
    public function __construct(public readonly string $name, public readonly bool $optional = false) {
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $name) !== 1) {
            throw new \InvalidArgumentException("Invalid argument name: $name");
        }
    }
    
    final public function getName() : string{ return $this->name; }
    
    final public function isOptional() : bool{ return $this->optional; }
    
    public function isGreedy() : bool{ return false; }
    
    public function canParse(string $value, CommandSender $sender) : bool{
        try {
            $this->parse($value, $sender);
            
            return true;
        } catch (ArgumentException) {
            return false;
        }
    }
    
    abstract public function parse(string $value, CommandSender $sender) : mixed;
    
    abstract public function toNetwork(string $scope, array $playerNames = []) : CommandParameter;
    
}