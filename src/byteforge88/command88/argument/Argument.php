<?php

declare(strict_types=1);

namespace byteforge88\command88\argument;

use byteforge88\command88\BaseCommand;

final class Argument {
    
    private static array $commands = [];
    
    private function __construct() {
        //NOOP
    }

    public static function within(BaseCommand $command, \Closure $configure) : void{
        self::$commands[] = $command;
        
        try {
            $configure();
        } finally {
            array_pop(self::$commands);
        }
    }

    public static function add(int $position, BaseArgument $argument) : void{
        $command = self::$commands === [] ? null : self::$commands[array_key_last(self::$commands)];
        
        if ($command === null) {
            throw new \LogicException('Use Argument static methods inside configure().');
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
