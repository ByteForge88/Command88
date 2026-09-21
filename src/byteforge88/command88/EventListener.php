<?php

declare(strict_types=1);

namespace byteforge88\command88;

use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketSendEvent;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketAssembler;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketDisassembler;

class EventListener implements Listener{
    
    public function __construct(private Command88 $plugin) {
        
    }

    /** @priority HIGH */
    public function onCommandData(DataPacketSendEvent $event) : void{
        $viewers = [];
        
        foreach ($event->getTargets() as $session) {
            $player = $session->getPlayer();
            
            if ($player === null) {
                return;
            }
            
            $viewers[] = $player;
        }
        
        if ($viewers === []) {
            return;
        }
        
        $packets = $event->getPackets();
        
        foreach ($packets as $index => $packet) {
            if (!$packet instanceof AvailableCommandsPacket) {
                continue;
            }
            
            $decoded = AvailableCommandsPacketDisassembler::disassemble($packet);
            $commands = [];
            $changed = false;
            
            foreach ($decoded->commandData as $data) {
                $command = $this->plugin->getPlugin()->getServer()->getCommandMap()->getCommand($data->getName());
                
                if ($command instanceof BaseCommand && $command->getOwningPlugin() === $this->plugin->getPlugin()) {
                    $changed = true;
                    $overloads = $command->getOverloads($viewers, 'Command88_' . $data->getName());
                    
                    if ($overloads === []) {
                        continue;
                    }
                    
                    $data->overloads = $overloads;
                }
                
                $commands[] = $data;
            }
            
            if ($changed) {
                $replacement = AvailableCommandsPacketAssembler::assemble($commands, array_values($decoded->unusedHardEnums), array_values($decoded->unusedSoftEnums));
                $replacement->senderSubId = $packet->senderSubId;
                $replacement->recipientSubId = $packet->recipientSubId;
                $packets[$index] = $replacement;
            }
        }
        
        $event->setPackets($packets);
    }

    public function onPluginDisable(PluginDisableEvent $event) : void{
        if($event->getPlugin() === $this->plugin->getPlugin()){
            Command88::unregister($this->plugin->getPlugin());
        }
    }
}
