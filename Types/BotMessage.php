<?php

namespace Types;

interface BotMessage
{
    public function getContent() : string;
}