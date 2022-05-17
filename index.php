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
    case '/kp':
        $jskp = DB::getMetaByKey('jak_sie_konczy_palic');
        Utils::convertImageFromBase64($jskp, $bot, $update->message->chat->id);
        break;
    case '/menu':
        $jskp = DB::getMetaByKey('menu_szuflada');
        Utils::convertImageFromBase64($jskp, $bot, $update->message->chat->id);
        break;
    case '/tpiec':
        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => 'Pobieram temperature z pieca'
        ]);
        DB::updateDeviceNewStatus(2, Status::WYSLIJ_WARTOSC);
        break;
    case '/przypomnij':
        $args = $update->message->getArgs();
        if(count($args) < 2)
        {
            $bot->sendMessage(['text' => utf8_encode('Zla ilosc parametrow'), 'chat_id' => $update->message->chat->id]);
            die;
        }

        $date = array_shift($args);
        $last = array_pop($args);
        $repeat = ($last == 'r') ? 1 : 0;
        $text = implode(' ', $args);
        if($repeat == 0)
            $text .= ' '.$last;
        DB::addNewRemind($date, $text, $repeat);
        $bot->sendMessage(['text' => utf8_encode('Dodano przypomnienie na dzien '.$date), 'chat_id' => $update->message->chat->id]);
        break;
    case '/pokazprzypomnienia':
        $reminds = DB::getAllReminds();
        $bot->sendMessage(['text' => implode(PHP_EOL, array_map(function($el){
            return utf8_encode(($el['cyclical'] ? 'cykliczne ': '').$el['datetime'].' '.$el['content']);
        }, $reminds)), 'chat_id' => $update->message->chat->id]);
        break;    
    case '/start':
    case '/help':
        $bot->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => utf8_encode('/zamknij - zamyka s'.PHP_EOL
                        .'/otworz - otwiera s'.PHP_EOL
                        .'/help - komendy'.PHP_EOL
                        .'/hasla - hasla do rzeczy'.PHP_EOL
                        .'/kp - instrukcja jak się konczy palic'.PHP_EOL
                        .'/tpiec - pobranie temperatury z pieca'.PHP_EOL
                        .'/przypomnij - ustaw przypomnienie rok-miesiac-dzien Tresc [r]'.PHP_EOL
                        .'/pokazprzypomnienia - lista przypomnien'.PHP_EOL
                        .'/menu - menu szuflady'.PHP_EOL
                        .'/getchatid - id chatu')
        ]);
        break;

}
die;