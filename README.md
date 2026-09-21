# TODO

# API (TODO)

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

class RewardCommand extends BaseCommand {

    protected function configure() : void{
        $this->setPermission('myplugin.reward');
        $this->setUsage('/{command} <target> <amount> [reason]');

        Argument::player(0, new PlayerArgument('target'));
        Argument::integer(1, new IntegerArgument('amount', min: 1, max: 1000));
        Argument::text(2, new TextArgument('reason', true));
    }

    protected function onSent(CommandSender $sender, string $label, array $args) : void{
        $target = $args['target']; //player object
        $amount = (string) $args['amount']; //cast this to string if need to
        $reason = $args['reason'] ?? 'No reason provided';

        $sender->sendMessage("You have gave {$target->getName()} {amount} for {$reason}!");
    }
}
```