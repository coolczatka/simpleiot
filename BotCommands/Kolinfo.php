<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Photo;

class Kolinfo extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('kol', 'Kol');
    }

    public function execute(array $parameters): BotMessage
    {
        $jskp = \DB::getMetaByKey('call');
        return new Photo(\Utils::convertImageFromBase64($jskp));
    }
}