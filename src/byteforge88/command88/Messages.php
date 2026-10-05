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

class Messages{
    
    public const DEFAULTS = [
        "usage" => "§eUsage: {usage}",
        "error" => "§c{error}",
        "no-permission" => "§cYou do not have permission to use this command!",
        "players-only" => "§cUse this command in-game!",
        "unknown-subcommand" => "§cUnknown subcommand: {subcommand}",
        "missing-argument" => "§cMissing argument: {argument}",
        "too-many-arguments" => "§cToo many arguments!",
        "invalid-integer" => "§c{argument} must be a whole number!",
        "integer-range" => "§c{argument} must be between {min} and {max}!",
        "invalid-float" => "§c{argument} must be a finite number!",
        "invalid-choice" => "§c{argument} must be one of: {choices}",
        "player-not-found" => "§c{argument}: select exactly one online player",
        "target-not-found" => "§c{argument}: no matching targets",
        "invalid-selector" => "§c{argument}: use a player name or @s, @a, @e, @p, @r without filters",
        "selector-player-only" => "§c{argument}: this selector needs an in-game sender"
    ];
    
    private function __construct(){
        //NOOP
    }

    public static function get(string $key, array $values = []) : string{
        $replace = [];
        
        foreach($values as $name => $value){
            $replace['{' . $name . '}'] = (string) $value;
        }
        
        return strtr(self::DEFAULTS[$key] ?? $key, $replace);
    }
}