<?php

require __DIR__ . '/vendor/autoload.php';
require 'Status.php';
require 'DB.php';
require 'Crypt.php';
require 'Utils.php';

use TelegramBot\TelegramBot;
use Dotenv\Dotenv;

$DEVICE_ID = 1;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret = $_ENV['TELEGRAM_SECRET'];
$bot = new TelegramBot($secret);

$update = $bot->getWebhookUpdate();
$app = new BotDriver();

try {
    $app->dispatch($bot, $update->message);
}
catch(Exception $e) {
    $bot->sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => $e->getMessage()
    ]);
}
die;