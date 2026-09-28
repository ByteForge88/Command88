# Command88

Command88 is a command API for PocketMine-MP that makes creating commands with proper arguments a little easier.

Instead of manually checking every value inside `execute()`, Command88 lets you define your command arguments ahead of time and gives you the parsed values when the command is sent.

It also handles the command data sent to Bedrock clients, so arguments can show up properly in the command autocomplete menu.

## Features

- Simple command API built on top of PocketMine-MP
- Typed command arguments
- Required and optional arguments
- Player arguments
- Integer arguments
- Text arguments
- Enum arguments
- Custom arguments
- Argument validation and parsing
- Minimum and maximum values for supported arguments
- Bedrock command autocomplete support
- Player selector support
- Add, remove, and manage command arguments
- Parsed arguments are available by their argument name
- Designed to keep command classes clean instead of putting everything inside `execute()`

## API

Commands extend `BaseCommand`.

There are two main methods you normally need to use:

### `configure()`

This is where the command is set up.

Permissions, usage, arguments, and other command information can be defined here.

### `onSent()`

This is called after Command88 has handled the arguments.

Instead of having to manually parse everything from `$args`, values can be accessed using the name given to the argument.

## Example

```php
<?php

declare(strict_types=1);

namespace byteforge88\myplugin\command;

use byteforge88\command88\BaseCommand;
use byteforge88\command88\argument\Argument;
use byteforge88\command88\argument\PlayerArgument;
use byteforge88\command88\argument\IntegerArgument;
use byteforge88\command88\argument\TextArgument;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RewardCommand extends BaseCommand {

    protected function configure() : void{
        $this->setPermission("myplugin.reward");
        $this->setUsage("/{command} <target> <amount> [reason]");

        Argument::player(0, new PlayerArgument("target"));
        Argument::integer(1, new IntegerArgument("amount", min: 1, max: 1000));
        Argument::text(2, new TextArgument("reason", true));
    }

    protected function onSent(CommandSender $sender, string $label, array $args) : void{
        /** @var Player $target */
        $target = $args["target"];

        $amount = $args["amount"];
        $reason = $args["reason"] ?? "No reason provided";

        $sender->sendMessage("You gave {$target->getName()} {$amount} for {$reason}!");
    }
}
```

The argument name is used as the key inside `$args`.

For example:

```php
new PlayerArgument("target")
```

can later be accessed with:

```php
$target = $args["target"];
```

This keeps the actual command logic separate from argument parsing.

## Arguments

Command88 provides different argument types depending on what your command needs.

Some examples include:

```php
Argument::player(...);
Argument::integer(...);
Argument::text(...);
```

Arguments use a position starting from `0`.

For example:

```php
Argument::player(0, new PlayerArgument("target"));
Argument::integer(1, new IntegerArgument("amount"));
Argument::text(2, new TextArgument("reason", true));
```

would represent:

```text
/reward <target> <amount> [reason]
```

The final `reason` argument is optional.

## Custom Arguments

If one of the built-in argument types doesn't fit what you need, a custom argument can be created with your own parser.

This makes it possible to add plugin-specific argument types without having to modify Command88 itself.

For example, a custom argument could be used for things such as:

- Kits
- Arenas
- Ranks
- Worlds
- Game modes
- Plugin-specific objects

## Why Command88?

PocketMine's normal command system works fine, but commands with several arguments can quickly turn into a lot of manual checks.

A command can end up doing things like:

```php
if (!isset($args[0])) {
    return;
}

$player = $server->getPlayerExact($args[0]);

if ($player === null) {
    return;
}

if (!isset($args[1]) || !is_numeric($args[1])) {
    return;
}
```

Command88 moves that kind of argument handling into the API so the command itself can focus on what it is actually supposed to do.

## Requirements

- PocketMine-MP (Axolotl fork works best)
- PHP 8.x

## Status

Command88 is still being developed, so parts of the API may change while new argument types and features are added.

If you find a bug or something doesn't behave correctly with the Bedrock command UI, feel free to open an issue.

## License

See the repository license for licensing information.