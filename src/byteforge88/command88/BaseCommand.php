<?php

declare(strict_types=1);

namespace byteforge88\command88;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use pocketmine\lang\Translatable;

use pocketmine\player\Player;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;

use byteforge88\command88\argument\Argument;
use byteforge88\command88\argument\BaseArgument;
use byteforge88\command88\argument\ArgumentList;
use byteforge88\command88\argument\ArgumentException;

abstract class BaseCommand extends Command implements PluginOwned {
    
    private ArgumentList $arguments;
    
    private array $subCommands = [];
    
    private array $subNames = [];
    private ?self $parent = null;
    private bool $configuring = true;
    private bool $playerOnly = false;
    private bool $customUsage = false;

    final public function __construct(
        protected PluginBase $plugin,
        string $name,
        string $description = '',
        array $aliases = []
    ) {
        Command88::getInstance($plugin);
        self::checkName($name);
        parent::__construct($name, $description, null, $aliases);
        $this->setPermission('command88.use');
        $this->arguments = new ArgumentList();
        
        try {
            Argument::within($this, function() : void{ $this->configure(); });
        } finally {
            $this->configuring = false;
        }
    }

    abstract protected function configure() : void;

    protected function onSent(CommandSender $sender, string $label, array $args) : void{
        $this->sendUsage($sender, $label);
    }

    final public function getOwningPlugin() : Plugin{ return $this->plugin; }
    
    final public function isSubCommand() : bool{ return $this->parent !== null; }
    
    final public function getArguments() : array{ return $this->arguments->all(); }
    
    final public function getSubCommands() : array{ return $this->subCommands; }

    private static function checkName(string $name) : void{
        if (preg_match('/^[a-z][a-z0-9_-]*$/D', $name) !== 1) {
            throw new \InvalidArgumentException('Use lowercase command names and aliases.');
        }
    }

    public function setAliases(array $aliases) : void{
        foreach ($aliases as $alias) {
            self::checkName($alias);
        }
        
        parent::setAliases(array_values(array_unique($aliases)));
    }

    private function checkConfiguring() : void{
        if (!$this->configuring) {
            throw new \LogicException('Define arguments and subcommands inside configure().');
        }
    }

    final protected function setPlayerOnly(bool $value = true) : void{
        $this->checkConfiguring();
        $this->playerOnly = $value;
    }

    final public function registerArgument(int $position, BaseArgument $argument) : void{
        $this->checkConfiguring();
        
         if ($this->subCommands !== []) {
             throw new \LogicException('Use arguments or subcommands on a node, not both.');
         }
        
        $this->arguments->register($position, $argument);
    }

    final public function removeArgument(int $position) : bool{
        if (!$this->arguments->remove($position)) {
            return false;
        }
        
        $this->argumentsChanged();
        return true;
    }

    final public function removeAllArguments() : void{
        if ($this->arguments->isEmpty()) {
            return;
        }
        
        $this->arguments->clear();
        $this->argumentsChanged();
    }

    private function argumentsChanged() : void{
        $root = $this;
        
        while ($root->parent !== null) {
            $root = $root->parent;
        }
        
        if (!$this->configuring && $root->isRegistered() && Command88::isRegistered($this->plugin)) {
            Command88::getInstance($this->plugin)->refresh();
        }
    }

    final protected function addArgument(BaseArgument $argument) : void{
        $this->registerArgument(count($this->arguments->all()), $argument);
    }

    final protected function addSubCommand(BaseCommand $command) : void{
        $this->checkConfiguring();
        
        if ($command === $this || $command->parent !== null || $command->isRegistered() || $command->configuring || $command->plugin !== $this->plugin) {
            throw new \LogicException('Use a fresh subcommand belonging to the same plugin.');
        }
        
        if (!$this->arguments->isEmpty()) {
            throw new \LogicException('Use arguments or subcommands on a node, not both.');
        }
        
        $names = array_unique([$command->getName(), ...$command->getAliases()]);
        
        foreach ($names as $name) {
            if (isset($this->subNames[$name])) {
                throw new \LogicException("Duplicate subcommand: $name");
            }
        }
        
        foreach ($names as $name) {
            $this->subNames[$name] = $command;
        }
        
        $command->parent = $this;
        $this->subCommands[] = $command;
    }

    public function setUsage(Translatable|string $usage) : void{
        $this->customUsage = true;
        parent::setUsage($usage);
    }

    public function getUsage() : Translatable|string{
        if (!$this->customUsage) {
            return $this->defaultUsage($this->getLabel());
        }
        
        $usage = parent::getUsage();
        return is_string($usage) ? str_replace('{command}', $this->getLabel(), $usage) : $usage;
    }

    private function defaultUsage(string $label) : string{
        return '/' . $label;
    }

    final public function usage(CommandSender $sender, ?string $label = null) : string{
        $label ??= $this->getLabel();
        
        if (!$this->customUsage) {
            return $this->defaultUsage($label);
        }
        
        $usage = parent::getUsage();
        
        if ($usage instanceof Translatable) {
            $usage = $sender->getLanguage()->translate($usage);
        }
        
        return str_replace('{command}', $label, $usage);
    }

    final public function sendUsage(CommandSender $sender, ?string $label = null) : void{
        $sender->sendMessage(Messages::get('usage', ['usage' => $this->usage($sender, $label)]));
    }

    public function testPermission(CommandSender $target, ?string $permission = null) : bool{
        if ($this->testPermissionSilent($target, $permission)) {
            return true;
        }
        
        $message = $this->getPermissionMessage();
        
        if ($message !== '') {
            $target->sendMessage($message === null ? Messages::get('no-permission') : strtr($message, [
                '<permission>' => $permission ?? implode(';', $this->getPermissions()),
                '{permission}' => $permission ?? implode(';', $this->getPermissions())
            ]));
        }
        
        return false;
    }

    final public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if (!Command88::isRegistered($this->plugin)) {
            return false;
        }
        
        if (!$this->testPermission($sender)) {
            return true;
        }
        
        if ($this->playerOnly && !$sender instanceof Player) {
            $sender->sendMessage(Messages::get('players-only'));
            return true;
        }
        
        if ($this->subCommands !== [] && $args !== []) {
            $name = strtolower(array_shift($args));
            
            if (!isset($this->subNames[$name])) {
                $sender->sendMessage(Messages::get('error', ['error' => Messages::get('unknown-subcommand', ['subcommand' => $name])]));
                $this->sendUsage($sender, $commandLabel);
                return true;
            }
            
            return $this->subNames[$name]->execute($sender, $commandLabel . ' ' . $name, $args);
        }
        
        try {
            $values = $this->arguments->parse($args, $sender);
        } catch (ArgumentException $e) {
            $sender->sendMessage(Messages::get('error', ['error' => $e->getMessage()]));
            $this->sendUsage($sender, $commandLabel);
            return true;
        }
        
        $this->onSent($sender, $commandLabel, $values);
        return true;
    }

    private function visibleTo(array $viewers) : bool{
        if (!Command88::isRegistered($this->plugin)) {
            return false;
        }
        
        foreach ($viewers as $viewer) {
            if (!$this->testPermissionSilent($viewer) || ($this->playerOnly && !$viewer instanceof Player)) {
                return false;
            }
        }
        
        return true;
    }

    final public function getOverloads(array $viewers, string $scope, array $prefix = [], ?array $playerNames = null) : array{
        if (!$this->visibleTo($viewers)) {
            return [];
        }
        
        $playerNames ??= [];
        
        if ($this->subCommands === []) {
            return $this->arguments->overloads($scope, $prefix, $playerNames);
        }
        
        $overloads = [new CommandOverload(false, $prefix)];
        
        foreach ($this->subCommands as $index => $sub) {
            $childScope = $scope . '_' . $index;
            $description = $sub->getDescription();
            
            if ($description instanceof Translatable) {
                $description = $viewers === [] ? $sub->getName() : $viewers[0]->getLanguage()->translate($description);
            }
            
            $label = trim($description) !== '' ? $description : $sub->getName();
            $literal = CommandParameter::enum($label, new CommandHardEnum($label, array_values(array_unique([$sub->getName(), ...$sub->getAliases()]))), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM);
            array_push($overloads, ...$sub->getOverloads($viewers, $childScope, [...$prefix, $literal], $playerNames));
        }
        
        return $overloads;
    }
}