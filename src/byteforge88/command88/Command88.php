<?php

/**
 *
 *       ____                                          _  ___   ___  
 *      / ___|___  _ __ ___  _ __ ___   __ _ _ __   __| |( _ ) ( _ ) 
 *     | |   / _ \| '_ ` _ \| '_ ` _ \ / _` | '_ \ / _` |/ _ \ / _ \ 
 *     | |__| (_) | | | | | | | | | | | (_| | | | | (_| | (_) | (_) |
 *      \____\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|\__,_|\___/ \___/ 
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ByteForge88
 *
 **/

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

class Command88{

    //update this eachtime AxolotlPM updates
    //along with api and version
    public const FORK_VERSION = "5.49.1";

    private static array $instances = [];
    
    private EventListener $listener;
    
    private bool $active = true;
    
    private ?TaskHandler $refreshTask = null;

    private PluginBase $plugin;

    private function __construct(PluginBase $plugin){
        $this->listener = new EventListener($this);
        $this->plugin = $plugin;
    }

    public static function init(PluginBase $plugin) : self{
        if(!$plugin->isEnabled()){
            throw new \LogicException("Call Command88::init($this) from onEnable()");
        }
        
        $id = spl_object_id($plugin);
        
        if(isset(self::$instances[$id])){
            return self::$instances[$id];
        }
        
        if(!class_exists(AvailableCommandsPacketAssembler::class) || !class_exists(AvailableCommandsPacketDisassembler::class)){
            throw new \RuntimeException("Command88 requires the command packet API from Axolotl-PM v" . self::FORK_VERSION);
        }
        
        $permissions = PermissionManager::getInstance();
        
        if($permissions->getPermission("command88.use") === null){
            $root = $permissions->getPermission(DefaultPermissions::ROOT_USER) ?? throw new \LogicException("PocketMine core permissions are not initialized!");

            //MIGHT remove this
            DefaultPermissions::registerPermission(new Permission("command88.use", "Public Command88 commands"), [$root]);
        }
        
        $manager = new self($plugin);
        
        try{
            $plugin->getServer()->getPluginManager()->registerEvents($manager->listener, $plugin);
            self::$instances[$id] = $manager;
        }catch(\Throwable $e){
            HandlerListManager::global()->unregisterAll($manager->listener);
            throw $e;
        }
        
        return $manager;
    }

    public static function isRegistered(PluginBase $plugin) : bool{
        return isset(self::$instances[spl_object_id($plugin)]) && $plugin->isEnabled();
    }

    public static function getInstance(PluginBase $plugin) : self{
        if(!self::isRegistered($plugin)){
            throw new \LogicException("Call Command88::init($this) before creating commands!");
        }
        
        return self::$instances[spl_object_id($plugin)];
    }

    public function getPlugin() : PluginBase{ return $this->plugin; }

    public function registerCommand(BaseCommand $command) : void{
        $this->assertActive();
        
        if($command->getOwningPlugin() !== $this->plugin){
            throw new \LogicException("Use the command owner\'s Command88 manager!");
        }
        
        if($command->isRegistered() || $command->isSubCommand()){
            throw new \LogicException("Register only unregistered root commands!");
        }
        
        $this->plugin->getServer()->getCommandMap()->register(strtolower($this->plugin->getName()), $command);
        $this->refresh();
    }

    public function registerAllCommands(array $commands) : void{
        foreach($commands as $command){
            $this->registerCommand($command);
        }
    }

    public function unregisterCommand(BaseCommand $command) : void{
        $this->assertActive();
        
        if($command->getOwningPlugin() !== $this->plugin){
            throw new \LogicException("Cannot remove another plugin\'s command!");
        }
        
        $this->plugin->getServer()->getCommandMap()->unregister($command);
        $this->refresh();
    }

    public function unregisterAllCommands(array $command) : void{
        foreach($commands as $command){
            $this->unregisterCommand($command);
        }
    }

    private function assertActive() : void{
        if(!$this->active || !$this->plugin->isEnabled()){
            throw new \LogicException("This Command88 registration is inactive.");
        }
    }

    public function refresh() : void{
        $this->assertActive();
        
        if($this->refreshTask !== null){
            return;
        }
        
        $this->refreshTask = $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void{
            $this->refreshTask = null;
            
            if($this->active && $this->plugin->isEnabled()){
                $this->syncClients();
            }
            
        }), 1);
    }

    private function syncClients() : void{
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }
    
    public static function unregister(PluginBase $plugin) : void{
        $id = spl_object_id($plugin);
        $manager = self::$instances[$id] ?? null;
        
        if($manager === null){
            return;
        }
        
        $manager->active = false;
        
        unset(self::$instances[$id]);
        $manager->refreshTask?->cancel();
        
        $manager->refreshTask = null;
        
        HandlerListManager::global()->unregisterAll($manager->listener);
        
        $map = $plugin->getServer()->getCommandMap();
        
        foreach($map->getCommands() as $command){
            if($command instanceof BaseCommand && $command->getOwningPlugin() === $plugin){
                $map->unregister($command);
            }
        }
        
        $manager->syncClients();
    }
}