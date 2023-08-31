<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Photo;

class EndOfBurningInst extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('kp', 'Instrukcja końca palenia');
    }

    public function execute(array $parameters) : BotMessage
    {
        $jskp = \DB::getMetaByKey('jak_sie_konczy_palic');
        $tempFile = \Utils::convertImageFromBase64($jskp);
        return new Photo($tempFile);
    }
}