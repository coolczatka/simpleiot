<?php

class Utils {
    public static function convertImageFromBase64($metaRow, &$bot, $chatid) {
        $photo = base64_decode($metaRow['value']);
        $extension = str_replace('image/', '', $metaRow['type']);
        $tempfile = fopen('temp.'.$extension, 'wb');
        fwrite($tempfile, $photo);
        fclose($tempfile);
        $ch = curl_file_create('temp.jpeg');
        $bot->sendPhoto([
            'chat_id' => $chatid,
            'photo' => $ch
        ]);
        unlink('temp.'.$extension);
    }
}