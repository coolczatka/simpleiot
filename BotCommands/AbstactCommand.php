<?php

namespace BotCommands;

use Types\BotMessage;

abstract class AbstactCommand
{
    protected $trigger = '';
    protected $description = '';

    /**
     * @param string $trigger
     */
    public function __construct(string $trigger, string $description)
    {
        $this->trigger = $trigger;
    }

    abstract public function execute(array $parameters) : BotMessage;

    /**
     * @return string
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }



}