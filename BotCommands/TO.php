<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class TO extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('tpiec', 'Pobranie temperatury z pieca');
    }

    public function execute(array $parameters) : BotMessage
    {
        \DB::updateDeviceNewStatus(2, \Status::WYSLIJ_WARTOSC);
        return new Text('Pobieram temperature z pieca');
    }
}