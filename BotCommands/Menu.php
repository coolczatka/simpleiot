<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Photo;

class Menu extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('menu', 'Menu szuflady');
    }

    public function execute(array $parameters) : BotMessage
    {
        $jskp = \DB::getMetaByKey('menu_szuflada');
        $tempFile = \Utils::convertImageFromBase64($jskp);
        return new Photo($tempFile);
    }
}