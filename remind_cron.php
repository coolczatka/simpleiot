<?php
if (php_sapi_name() == 'cli') {
	echo 'nic tu po tobie';
	die;
}

require __DIR__ . '/vendor/autoload.php';
require 'DB.php';

use TelegramBot\TelegramBot;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret = $_ENV['TELEGRAM_SECRET'];
$bot = new TelegramBot($secret);
$chatId = $_ENV['CHATID'];
$chatIdAlt = $_ENV['ALT_CHATID'];
$reminds = DB::getReminds();
foreach($reminds as $remind)
{
	$bot->sendMessage([
   		'chat_id' => $chatId,
   		'text' => utf8_encode($remind[0])
	]);
}
die;