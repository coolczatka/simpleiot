<?php

class DB {
    protected static $pdo;

    protected static function getInstance() {
        if(isset(self::$pdo) && self::$pdo !== null) {
            return self::$pdo;
        }
        else {
            $host = $_ENV['DB_SERVER'];
            $name = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $type = $_ENV['DB_TYPE'];
            $port = $_ENV['DB_PORT'];
            $pdo = new PDO($type.':host=' . $host . ';dbname=' . $name . ';port=' . $port . ";charset=utf8", $user, $pass );
            self::$pdo = $pdo;
            return $pdo;
        }
    }

    public static function getDevice($id) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function updateDeviceStatus($id, $status) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE devices SET status=? WHERE id = ?");
        $stmt->bindParam(1, $status, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public static function updateDeviceNewStatus($id, $status) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE devices SET new_status=? WHERE id = ?");
        $stmt->bindParam(1, $status, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public static function addNewRemind($date, $text, $repeat = 0) {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("INSERT INTO reminds (`datetime`, `content`, `cyclical`) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $date, PDO::PARAM_STR);
        $stmt->bindParam(2, $text, PDO::PARAM_STR);
        $stmt->bindParam(3, $repeat, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }
    
    public static function getReminds() {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT content FROM `reminds` WHERE `datetime` = CURRENT_DATE() OR (cyclical = 1 AND day(datetime) = day(now()) AND month(datetime) = month(now()))");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public static function getAllReminds() {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM `reminds` WHERE datetime > now() OR cyclical = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public static function insertMetadata($key, $value, $type, $encrypted, $label)
    {
        $pdo = self::getInstance();
        $crypt = new Crypt($_ENV['AESKEY']);
        $newvalue = '';
        if($encrypted){
            $newvalue = $crypt->encrypt($value);
        }
        else {
            $newvalue = $value;
        }

        $stmt = $pdo->prepare("INSERT INTO metadata (`key`, `value`, `type`, `encrypted`, `label`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $key, PDO::PARAM_STR);
        $stmt->bindParam(2, $newvalue, PDO::PARAM_STR);
        $stmt->bindParam(3, $type, PDO::PARAM_STR);
        $stmt->bindParam(4, $encrypted, PDO::PARAM_INT);
        $stmt->bindParam(5, $label, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    public static function getMetaList($type)
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM metadata WHERE `type` = ?");
        $stmt->bindParam(1, $type, PDO::PARAM_STR);
        $stmt->execute();
        $metadata = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $crypt = new Crypt($_ENV['AESKEY']);
        foreach($metadata as &$row) {
            if((bool)$row['encrypted'])
                $row['value'] = $crypt->decrypt($row['value']);
        }
        return $metadata;
    }

    public static function getMetaByKey($key)
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM metadata WHERE `key`= ?");
        $stmt->bindParam(1, $key, PDO::PARAM_STR);
        $stmt->execute();
        $metadata = $stmt->fetch();
        $crypt = new Crypt($_ENV['AESKEY']);
        $metadata['value'] = $metadata['encrypted'] ? $crypt->decrypt($metadata['value']) : $metadata['value'];
        return $metadata;
    }
}