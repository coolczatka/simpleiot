<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class ShowRemind extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('pokazprzypomnienia', 'Lista przypomnien');
    }

    public function execute(array $parameters) : BotMessage
    {
        $reminds = \DB::getAllReminds();
        return new Text(implode(PHP_EOL, array_map(function($el){
            return utf8_encode(($el['cyclical'] ? 'cykliczne ': '').$el['datetime'].' '.$el['content']);
        }, $reminds)));
    }
}