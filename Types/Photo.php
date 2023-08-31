<?php

namespace Types;

class Photo implements BotMessage
{
    protected $filepath;

    /**
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }


    /**
     * @return mixed
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    public function __destruct()
    {
        \Utils::purgeTemp($this->filepath);
    }

    public function getContent(): string
    {
        return $this->filepath;
    }
}