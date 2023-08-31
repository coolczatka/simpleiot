<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class AI extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('ai', 'ChatGPT');
    }

    public function execute(array $parameters): BotMessage
    {
        $openAiKey = $_ENV['OPENAI_API_KEY'];
        $openAi = new \Orhanerday\OpenAi\OpenAi($openAiKey);
        $options = [];
        if($parameters[0] == '--options') {
            $options = json_decode($parameters[1], true);
            array_shift($parameters);
            array_shift($parameters);
        }
        $messageForBot = implode(' ', $parameters);

        $complete = $openAi->completion(array_merge([
            'model' => 'text-davinci-003',
            'prompt' => $messageForBot,
            'temperature' => 0.2,
            'max_tokens' => 1000,
//                'frequency_penalty' => 0,
//                'presence_penalty' => 0.6,
        ], $options));

        return new Text(json_decode($complete,true)['choices'][0]['text']);
    }
}