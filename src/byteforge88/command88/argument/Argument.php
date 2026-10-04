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

namespace byteforge88\command88\argument;

use byteforge88\command88\BaseCommand;

class Argument{
    
    private static array $commands = [];
    
    private function __construct(){
        //NOOP
    }

    public static function within(BaseCommand $command, \Closure $configure) : void{
        self::$commands[] = $command;
        
        try{
            $configure();
        }finally{
            array_pop(self::$commands);
        }
    }

    public static function add(int $position, BaseArgument $argument) : void{
        $command = self::$commands === [] ? null : self::$commands[array_key_last(self::$commands)];
        
        if($command === null){
            throw new \LogicException("Use Argument static methods inside configure()");
        }
        
        $command->registerArgument($position, $argument);
    }
    
    public static function string(int $position, StringArgument $argument) : void{ self::add($position, $argument); }
    
    public static function text(int $position, TextArgument $argument) : void{ self::add($position, $argument); }
    
    public static function integer(int $position, IntegerArgument $argument) : void{ self::add($position, $argument); }
    
    public static function float(int $position, FloatArgument $argument) : void{ self::add($position, $argument); }
    
    public static function boolean(int $position, BooleanArgument $argument) : void{ self::add($position, $argument); }
    
    public static function player(int $position, PlayerArgument $argument) : void{ self::add($position, $argument); }
    
    public static function target(int $position, TargetArgument $argument) : void{ self::add($position, $argument); }
    
    public static function enum(int $position, EnumArgument $argument) : void{ self::add($position, $argument); }
    
    public static function suggest(int $position, SoftEnumArgument $argument) : void{ self::add($position, $argument); }
    
    public static function custom(int $position, CustomArgument $argument) : void{ self::add($position, $argument); }
    
}