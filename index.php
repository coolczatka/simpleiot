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
try {
    switch($command) {
        case '/s_zamknij':
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'Ok zamykam!'
            ]);
            DB::updateDeviceNewStatus($DEVICE_ID, Status::DRZWI_ZAMKNIETE);
            break;
        case '/s_otworz':
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'Ok otwieram!'
            ]);
            DB::updateDeviceNewStatus($DEVICE_ID, Status::DRZWI_OTWARTE);
            break;
        case '/hasla':
            $args = $update->message->getArgs();

            if(count($args) > 1) {
                $function = array_shift($args);
                if($function === 'dodaj') {
                    $key = array_shift($args);
                    $valueLabel = implode(' ', $args);
                    $exploded = explode('||||', $valueLabel);
                    $value = $exploded[0];
                    $label = $exploded[1];
                    DB::insertMetadata($key, $value, 'password', 1, $label);
                    $bot->sendMessage(['text' => utf8_encode('ok dodaje'), 'chat_id' => $update->message->chat->id]);
                }

                if($function === 'usun') {
                    $pattern = array_shift($args);
                    DB::deleteMetadataLike($pattern);
                    $bot->sendMessage(['text' => utf8_encode('ok usuwam'), 'chat_id' => $update->message->chat->id]);
                }
            }
            elseif(count($args) === 1) {
                //podpowiedzi
                $function = array_shift($args);
                if($function === 'dodaj')
                    $bot->sendMessage(['text' => utf8_encode('dodaj [klucz wartosc||||etykieta]'), 'chat_id' => $update->message->chat->id]);
                elseif ($function === 'usun')
                    $bot->sendMessage(['text' => utf8_encode('usun [klucz|etykieta]'), 'chat_id' => $update->message->chat->id]);
                else
                    $bot->sendMessage(['text' => utf8_encode('Nieznana funkcja'), 'chat_id' => $update->message->chat->id]);
                die;
            }
            else {
                $passwords = DB::getMetaList('password');
                $x = '';
                foreach($passwords as $password)
                {
                    $x .= $password['label'] .': ||'.$password['value'] .'|| '. PHP_EOL;
                }

                $bot->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => str_replace(
                        ['.', '_'], 
                        ['\.', '\_'], 
                        utf8_encode($x)),
                    'parse_mode' => 'MarkdownV2'
                ]);
            }
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
        case '/bilet':
            $videofilename = $_SERVER['SERVER_NAME'] . '/' . 'bot/' . 'static/'.'bilet.mp4';
            try {
                $bot->sendVideo(['video' => $videofilename, 'chat_id' => $update->message->chat->id]);
            }
            catch(Exception $e){$bot->sendMessage(['text' => $e->getMessage(), 'chat_id' => $update->message->chat->id]);}
            break;
        case '/kol':
            $jskp = DB::getMetaByKey('call');
            Utils::convertImageFromBase64($jskp, $bot, $update->message->chat->id);
            break;
        case '/niekol':
            $jskp = DB::getMetaByKey('no_call');
            Utils::convertImageFromBase64($jskp, $bot, $update->message->chat->id);
            break;
        case '/ai':
            $openAiKey = $_ENV['OPENAI_API_KEY'];
            $openAi = new \Orhanerday\OpenAi\OpenAi($openAiKey);
            $args = $update->message->getArgs();
            $options = [];
            if($args[0] == '--options') {
                $options = json_decode($args[1], true);
                array_shift($args);
                array_shift($args);
            }
            $messageForBot = implode(' ', $args);

            $complete = $openAi->completion(array_merge([
                'model' => 'text-davinci-003',
                'prompt' => $messageForBot,
                'temperature' => 0.2,
                'max_tokens' => 1000,
//                'frequency_penalty' => 0,
//                'presence_penalty' => 0.6,
            ], $options));

            $bot->sendMessage(['text' => json_decode($complete,true)['choices'][0]['text'], 'chat_id' => $update->message->chat->id]);
            break;
        case '/start':
        case '/help':
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => utf8_encode('/zamknij - zamyka s'.PHP_EOL
                            .'/otworz - otwiera s'.PHP_EOL
                            .'/help - komendy'.PHP_EOL
                            .'/hasla - hasla do rzeczy [usun|dodaj]'.PHP_EOL
                            .'/ai [--options json] tekst - wysłanie pytania do openai'.PHP_EOL
                            .'/kp - instrukcja jak się konczy palic'.PHP_EOL
                            .'/tpiec - pobranie temperatury z pieca'.PHP_EOL
                            .'/przypomnij - ustaw przypomnienie rok-miesiac-dzien Tresc [r]'.PHP_EOL
                            .'/pokazprzypomnienia - lista przypomnien'.PHP_EOL
                            .'/menu - menu szuflady'.PHP_EOL
                            .'/bilet - instrukcja pobierania biletu'.PHP_EOL
                            .'/kol - info call'.PHP_EOL
                            .'/niekol - info nie call'.PHP_EOL
                            .'/getchatid - id chatu')
            ]);
            break;

    }
}
catch(Exception $e) {
    $bot->sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => $e->getMessage()
    ]);
}
die;