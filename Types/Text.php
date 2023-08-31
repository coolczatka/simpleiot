<?php

namespace Types;

class Text implements BotMessage
{
    protected $text;

    /**
     * @param $filepath
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getContent(): string
    {
        return $this->text;
    }
}