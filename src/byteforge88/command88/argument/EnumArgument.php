<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\data\CommandEnum;

use byteforge88\command88\Messages;

class EnumArgument extends BaseArgument{
    
    public readonly CommandEnum $enum;
    
    public function __construct(private string $name, array|CommandEnum $values, private bool $optional = false) {
        parent::__construct($name, $optional);
        $this->enum = is_array($values) ? new CommandEnum($values) : $values;
    }
    
    public function parse(string $value, CommandSender $sender) : mixed{
        if (!$this->enum->soft && !in_array($value, $this->enum->values, true)) {
            throw new ArgumentException(Messages::get('invalid-choice', ['argument' => $this->name, 'choices' => implode(', ', $this->enum->values)]));
        }
        
        return $value;
    }
    
    protected function networkTypeName() : string{ return 'string'; }

    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        $enum = $this->enum->toNetwork($this->enum->soft ? $scope . '_' . $this->name : $this->networkTypeName());
        
        return $this->enum->soft
            ? CommandParameter::softEnum($this->name, $enum, 0, $this->optional)
            : CommandParameter::enum($this->name, $enum, 0, $this->optional);
    }
}