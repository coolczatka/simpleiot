<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class Remind extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('przypomnij', 'Dodaje przypomnienie');
    }

    public function execute(array $parameters) : BotMessage
    {
        if(count($parameters) < 2)
        {
            return new Text(['text' => utf8_encode('Zla ilosc parametrow')]);
        }

        $date = array_shift($parameters);
        $last = array_pop($parameters);
        $repeat = ($last == 'r') ? 1 : 0;
        $text = implode(' ', $parameters);
        if($repeat == 0)
            $text .= ' '.$last;
        \DB::addNewRemind($date, $text, $repeat);
        return new Text(utf8_encode('Dodano przypomnienie na dzien '.$date));
    }
}