<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Video;

class TicketInst extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('bilet', 'Instrukcja kupowania biletu');
    }

    public function execute(array $parameters): BotMessage
    {
        return new Video($_SERVER['SERVER_NAME'] . '/' . 'bot/' . 'static/'.'bilet.mp4');
    }
}