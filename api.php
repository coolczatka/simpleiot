<?php
require __DIR__ . '/vendor/autoload.php';
require 'DB.php';
require 'Status.php';

use TelegramBot\TelegramBot;
use Dotenv\Dotenv;

class Api {

    protected $bot;
    
    protected $statuses = [
        Status::URZADZENIE_WYLACZONE => 'Urządzenie wyłączone',
        Status::DRZWI_ZAMKNIETE => 'Drzwi zamknięte!',
        Status::DRZWI_OTWARTE => 'Drzwi otwarte!'
    ];

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $secret = $_ENV['TELEGRAM_SECRET'];
        $this->bot = new TelegramBot($secret);
    }

    public function processRequests() {
        
        if(!$this->checkAuth()) {
            header("Status: 401 Unauthorized");
            echo json_encode(['status' => 401, 'error' => true]);
            die;
        }
        try {
            switch($_REQUEST['akcja']) {
                case 'getDevice': 
                    $deviceId = $_REQUEST['id'];
                    $device = DB::getDevice($deviceId);
                    echo json_encode([
                        'id' => $device['id'],
                        'nazwa' => $device['nazwa'],
                        'status' => $device['status'],
                        'new_status' => $device['new_status']
                    ]);
                    break;
                case 'updateStatus':
                    $status = $_REQUEST['status'];
                    $deviceId = $_REQUEST['id'];
                    $ok = DB::updateDeviceStatus($deviceId, $status);
                    if(!$ok){
                        header("Status: 400 Bad request");
                        echo json_encode(['status' => 400, 'error' => true]);
                        die;
                    }
                    $this->bot->sendMessage([
                        'chat_id' => $_ENV['CHATID'],
                        'text' => $this->statuses[$status]
                    ]);
                    echo json_encode(['status' => 200, 'updated' => true]);
                    break;
                default:
                    header("Status: 400 Bad request");
                    echo json_encode(['status' => 400, 'error' => true]);
            }
            die;
        }
        catch(PDOException $e) {
            header("Status: 500 Internal Server Error");
            echo json_encode(['status' => 500, 'error' => true]);
            die;
        }

    }

    protected function checkAuth() {

        $headers = getallheaders();
        $key = $headers['x-api-key'];
        $secret = $_ENV['APIKEY'];

        if($key !== $secret)
            return false;
        return true;
    }
}

$api = new Api();
$api->processRequests();