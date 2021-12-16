<?php

require __DIR__ . '/vendor/autoload.php';
require 'Status.php';
require 'DB.php';
require 'Crypt.php';

use TelegramBot\TelegramBot;
use Dotenv\Dotenv;

$DEVICE_ID = 1;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret = $_ENV['TELEGRAM_SECRET'];
$bot = new TelegramBot($secret);

$update = $bot->getWebhookUpdate();
$command = $update->message->getCommand();
$chatId = $_ENV['CHATID'];
$chatIdAlt = $_ENV['ALT_CHATID'];

if($command == '/getchatid') {
    $bot->sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => $update->message->chat->id
    ]);
}
if(!in_array($update->message->chat->id, [$chatId, $chatIdAlt])) {
    $bot->sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => 'Nie możesz nic zrobić tym botem. Nic tu po tobie!'
    ]);
    die;
}
switch($command) {
    case '/stajenka_zamknij':
        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => 'Ok zamykam!'
        ]);
        DB::updateDeviceNewStatus($DEVICE_ID, Status::DRZWI_ZAMKNIETE);
        break;
    case '/stajenka_otworz':
        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => 'Ok otwieram!'
        ]);
        DB::updateDeviceNewStatus($DEVICE_ID, Status::DRZWI_OTWARTE);
        break;
    case '/hasla':
        $passwords = DB::getMetaList('password');
        $x = '';
        foreach($passwords as $password)
        {
            $x .= $password['label'] .': '.$password['value'] . PHP_EOL;
        }

        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => utf8_encode($x)
        ]);
        break;

    case '/start':
    case '/help':
        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => '/stajenka_zamknij - zamyka stajnie'.PHP_EOL
                        .'/stajenka_otworz - otwiera stajnie'.PHP_EOL
                        .'/help - komendy'.PHP_EOL
			.'/hasla - hasla do rzeczy'.PHP_EOL
                        .'/getchatid - id chatu'
        ]);
        break;
}

die;