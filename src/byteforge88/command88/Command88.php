<?php

declare(strict_types=1);

namespace byteforge88\command88;

use pocketmine\plugin\PluginBase;

use pocketmine\event\HandlerListManager;

use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketAssembler;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketDisassembler;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class Command88 {

    private static array $instances = [];
    
    private EventListener $listener;
    
    private bool $active = true;
    
    private ?TaskHandler $refreshTask = null;

    private function __construct(private PluginBase $plugin){
        $this->listener = new EventListener($this);
    }

    public static function init(PluginBase $plugin) : self{
        if (!$plugin->isEnabled()) {
            throw new \LogicException('Call Command88::register($this) from onEnable().');
        }
        
        $id = spl_object_id($plugin);
        
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }
        
        if (!class_exists(AvailableCommandsPacketAssembler::class) || !class_exists(AvailableCommandsPacketDisassembler::class)) {
            throw new \RuntimeException('Command88 requires the command packet API from Axolotl-PM {version}.');
        }
        
        $permissions = PermissionManager::getInstance();
        
        if ($permissions->getPermission('command88.use') === null) {
            $root = $permissions->getPermission(DefaultPermissions::ROOT_USER)
                ?? throw new \LogicException('PocketMine core permissions are not initialized.');
            
            DefaultPermissions::registerPermission(new Permission('command88.use', 'Public Command88 commands'), [$root]);
        }
        
        $manager = new self($plugin);
        
        try {
            $plugin->getServer()->getPluginManager()->registerEvents($manager->listener, $plugin);
            self::$instances[$id] = $manager;
        } catch (\Throwable $e) {
            HandlerListManager::global()->unregisterAll($manager->listener);
            throw $e;
        }
        
        return $manager;
    }

    public static function isRegistered(PluginBase $plugin) : bool{
        return isset(self::$instances[spl_object_id($plugin)]) && $plugin->isEnabled();
    }

    public static function getInstance(PluginBase $plugin) : self{
        if (!self::isRegistered($plugin)) {
            throw new \LogicException('Call Command88::register($this) before creating commands.');
        }
        
        return self::$instances[spl_object_id($plugin)];
    }

    public function getPlugin() : PluginBase{ return $this->plugin; }

    public function registerCommand(BaseCommand $command) : void{
        $this->assertActive();
        
        if ($command->getOwningPlugin() !== $this->plugin) {
            throw new \LogicException('Use the command owner\'s Command88 manager.');
        }
        
        if ($command->isRegistered() || $command->isSubCommand()) {
            throw new \LogicException('Register only unregistered root commands.');
        }
        
        $this->plugin->getServer()->getCommandMap()->register(strtolower($this->plugin->getName()), $command);
        $this->refresh();
    }

    public function registerAllCommands(array $commands) : void{
        foreach ($commands as $command) {
            $this->registerCommand($command);
        }
    }

    public function unregisterCommand(BaseCommand $command) : void{
        $this->assertActive();
        
        if ($command->getOwningPlugin() !== $this->plugin) {
            throw new \LogicException('Cannot remove another plugin\'s command.');
        }
        
        $this->plugin->getServer()->getCommandMap()->unregister($command);
        $this->refresh();
    }

    public function unregisterAllCommands(array $command) : void{
        foreach ($commands as $command) {
            $this->unregisterCommand($command);
        }
    }

    private function assertActive() : void{
        if (!$this->active || !$this->plugin->isEnabled()) {
            throw new \LogicException('This Command88 registration is inactive.');
        }
    }

    public function refresh() : void{
        $this->assertActive();
        
        if ($this->refreshTask !== null) {
            return;
        }
        
        $this->refreshTask = $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void{
            $this->refreshTask = null;
            
            if ($this->active && $this->plugin->isEnabled()) {
                $this->syncClients();
            }
            
        }), 1);
    }

    private function syncClients() : void{
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }
    
    public static function unregister(PluginBase $plugin) : void{
        $id = spl_object_id($plugin);
        $manager = self::$instances[$id] ?? null;
        
        if ($manager === null) {
            return;
        }
        
        $manager->active = false;
        
        unset(self::$instances[$id]);
        $manager->refreshTask?->cancel();
        
        $manager->refreshTask = null;
        
        HandlerListManager::global()->unregisterAll($manager->listener);
        
        $map = $plugin->getServer()->getCommandMap();
        
        foreach ($map->getCommands() as $command) {
            if ($command instanceof BaseCommand && $command->getOwningPlugin() === $plugin) {
                $map->unregister($command);
            }
        }
        
        $manager->syncClients();
    }
}
