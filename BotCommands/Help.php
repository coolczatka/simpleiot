<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class Help extends AbstactCommand
{

    public function __construct()
    {
        parent::__construct('help', 'Lista komend');
    }

    public function execute(array $parameters): BotMessage
    {
        $result = '';
        foreach(scandir(__DIR__) as $filename) {
            if($filename === '.' || $filename === '..' || $filename === 'AbstactCommand.php')
                continue;

            $classname = '\\'. basename(__DIR__) . '\\' . str_replace('.php', '', $filename);
            /** @var AbstactCommand $command */
            $command = new $classname();

            $result .= '/' . $command->getTrigger() . ' - ' . $command->getDescription() . PHP_EOL;
        }

        return new Text($result);
    }


}

(new Help())->execute([]);