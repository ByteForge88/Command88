<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket as Packet;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\Messages;

class IntegerArgument extends BaseArgument {
    
    public function __construct(
        private string $name,
        private bool $optional = false,
        public readonly ?int $min = null,
        public readonly ?int $max = null
    ) {
        parent::__construct($name, $optional);
        
        if ($min !== null && $max !== null && $min > $max) {
            throw new \InvalidArgumentException('Minimum exceeds maximum.');
        }
    }
    public function parse(string $value, CommandSender $sender) : mixed{
        if (preg_match('/^[+-]?[0-9]+$/D', $value) !== 1) {
            throw new ArgumentException(Messages::get('invalid-integer', ['argument' => $this->name]));
        }
        
        $negative = str_starts_with($value, '-');
        $digits = ltrim(ltrim($value, '+-'), '0');
        $digits = $digits === '' ? '0' : $digits;
        $limit = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;
        $overflow = strlen($digits) > strlen($limit) || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0);
        $number = (int) $value;
        
        if ($overflow || ($this->min !== null && $number < $this->min) || ($this->max !== null && $number > $this->max)) {
            throw new ArgumentException(Messages::get('integer-range', ['argument' => $this->name, 'min' => $this->min ?? PHP_INT_MIN, 'max' => $this->max ?? PHP_INT_MAX]));
        }
        
        return $number;
    }
    
    public function toNetwork(string $scope, array $playerNames = []) : CommandParameter{
        return CommandParameter::standard($this->name, Packet::ARG_TYPE_INT, 0, $this->optional);
    }
}
