<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Photo;

class NoKolinfo extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('niekol', 'Koniec kola');
    }

    public function execute(array $parameters): BotMessage
    {
        $jskp = \DB::getMetaByKey('no_call');
        return new Photo(\Utils::convertImageFromBase64($jskp));
    }
}