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

use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

use byteforge88\command88\Messages;

class ArgumentList{
    
    private array $positions = [];
    
    public function isEmpty() : bool{ return $this->positions === []; }
    
    public function all() : array{ return $this->positions; }

    public function remove(int $position) : bool{
        if(!isset($this->positions[$position])){
            return false;
        }
        
        array_splice($this->positions, $position, 1);
        return true;
    }

    public function clear() : void{ $this->positions = []; }

    public function register(int $position, BaseArgument $argument) : void{
        if($position < 0 || $position > count($this->positions)){
            throw new \LogicException("Argument positions must start at 0 and have no gaps!");
        }
        
        $next = $this->positions;
        $next[$position][] = $argument;
        $names = [];
        $optionalSeen = false;
        $signatures = 1;
        
        foreach($next as $index => $choices){
            $optional = $choices[0]->optional;
            
            if($optionalSeen && !$optional){
                throw new \LogicException("Required arguments must precede optional arguments!");
            }
            
            $optionalSeen = $optionalSeen || $optional;
            
            foreach($choices as $choice){
                if($choice->optional !== $optional){
                    throw new \LogicException("Alternatives at one position must agree on optional status!");
                }
                
                if(isset($names[$choice->name]) && $names[$choice->name] !== $index){
                    throw new \LogicException("Argument names cannot be reused at different positions!");
                }
                
                $names[$choice->name] = $index;
                
                if($choice->isGreedy() && $index !== count($next) - 1){
                    throw new \LogicException("Text arguments must be at the final position!");
                }
            }
            
            $signatures *= count($choices);
            
            if($signatures > 64){
                throw new \LogicException("At most 64 alternative argument signatures are supported per command!");
            }
        }
        
        $this->positions = $next;
    }

    public function parse(array $tokens, CommandSender $sender) : array{
        return $this->parsePosition($tokens, $sender, 0, 0);
    }

    private function parsePosition(array $tokens, CommandSender $sender, int $position, int $offset) : array{
        if(!isset($this->positions[$position])){
            if($offset < count($tokens)){
                throw new ArgumentException(Messages::get("too-many-arguments"));
            }
            
            return [];
        }
        
        $choices = $this->positions[$position];
        
        if(!array_key_exists($offset, $tokens)){
            if(!$choices[0]->optional){
                throw new ArgumentException(Messages::get("missing-argument", ["argument" => $choices[0]->name]));
            }
            
            $values = [];
            
            foreach($choices as $choice){
                $values[$choice->name] = null;
            }
            
            return $values + $this->parsePosition($tokens, $sender, $position + 1, $offset);
        }
        
        $choices = array_merge(
            array_values(array_filter($choices, fn(BaseArgument $arg) => !$arg->isGreedy())),
            array_values(array_filter($choices, fn(BaseArgument $arg) => $arg->isGreedy()))
        );
        
        $error = null;
        
        foreach($choices as $choice){
            try{
                $text = $choice->isGreedy() ? implode(" ", array_slice($tokens, $offset)) : $tokens[$offset];
                $value = $choice->parse($text, $sender);
                $nextOffset = $choice->isGreedy() ? count($tokens) : $offset + 1;
                $rest = $this->parsePosition($tokens, $sender, $position + 1, $nextOffset);
                
                return [$choice->name => $value] + $rest;
            }catch(ArgumentException $e){
                $error ??= $e;
            }
        }
        
        throw $error ?? new \LogicException("Empty argument position!");
    }

    public function overloads(string $scope, array $prefix, array $playerNames) : array{
        $signatures = [$prefix];
        
        foreach($this->positions as $position => $choices){
            $next = [];
            
            foreach($signatures as $signature){
                foreach($choices as $index => $choice){
                    $next[] = [...$signature, $choice->toNetwork($scope . "_p" . $position . "_a" . $index, $playerNames)];
                }
            }
            
            $signatures = $next;
        }
        
        return array_map(fn(array $parameters) => new CommandOverload(false, $parameters), $signatures);
    }
}