<?php

declare(strict_types=1);

namespace byteforge88\command88\data;

use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class CommandEnum {
    
    public readonly array $values;

    public function __construct(array $values, public readonly bool $soft = false){
        if (!$soft && $values === []) {
            throw new \InvalidArgumentException('A hard enum needs at least one choice.');
        }
        
        foreach ($values as $value) {
            if (!is_string($value) || preg_match('/^[a-zA-Z0-9_.:-]+$/D', $value) !== 1) {
                throw new \InvalidArgumentException('Enum values must be nonempty identifiers without spaces.');
            }
        }
        
        $this->values = array_values(array_unique($values));
    }

    public function toNetwork(string $name) : CommandHardEnum|CommandSoftEnum{
        return $this->soft ? new CommandSoftEnum($name, $this->values) : new CommandHardEnum($name, $this->values);
    }
}