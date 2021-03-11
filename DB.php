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

    public static function getDevice($id)
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function updateDeviceStatus($id, $status)
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE devices SET status=? WHERE id = ?");
        $stmt->bindParam(1, $status, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public static function updateDeviceNewStatus($id, $status)
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE devices SET new_status=? WHERE id = ?");
        $stmt->bindParam(1, $status, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }
}