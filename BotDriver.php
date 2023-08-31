<?php

use BotCommands\AbstactCommand;
use TelegramBot\Types\Message;

class BotDriver
{
    protected $commandCollection;

    public function __construct()
    {
        foreach(scandir(__DIR__) as $filename) {
            if($filename === '.' || $filename === '..' || $filename === 'AbstactCommand.php')
                continue;

            $classname = '\\'. basename(__DIR__) . '\\' . str_replace('.php', '', $filename);
            /** @var AbstactCommand $command */
            $command = new $classname();
            $this->commandCollection[$command->getTrigger()] = $command;

        }
    }

    public function dispatch(\TelegramBot\TelegramBot $bot, Message $message)
    {
        $this->guardAgainstUnauthorizedAccess($message);

        $command = $message->getCommand();
        $args = $message->getArgs();

        if(in_array($command, array_keys($this->commandCollection))) {
            /**
             * @var AbstactCommand $commandHandler
             */
            $commandHandler = (new $this->commandCollection[$command]());
            /**
             * @var \Types\BotMessage $botMessage
             */
            $botMessage = $commandHandler->execute($args);
            $botMessageType = get_class($botMessage);
            if(str_contains($botMessageType, 'Video')) {
                $bot->sendVideo([
                    ['video' => $botMessage->getContent(), 'chat_id' => $message->chat->id]
                ]);
            }
            elseif (str_contains($botMessageType, 'Photo')) {
                $bot->sendPhoto([
                    ['photo' => $botMessage->getContent(), 'chat_id' => $message->chat->id]
                ]);
            }
            elseif (str_contains($botMessageType, 'Text')) {
                $bot->sendMessage([
                    ['text' => $botMessage->getContent(), 'chat_id' => $message->chat->id]
                ]);
            }

        }
    }

    protected function guardAgainstUnauthorizedAccess(Message $message) {
        $chatId = $_ENV['CHATID'];
        $chatIdAlt = $_ENV['ALT_CHATID'];

        if(!in_array($message->chat->id, [$chatId, $chatIdAlt])) {
            die;
        }
    }
}