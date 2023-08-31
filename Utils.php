<?php

class Utils {
    public static function convertImageFromBase64($metaRow) {
        $photo = base64_decode($metaRow['value']);
        $extension = str_replace('image/', '', $metaRow['type']);
        $tempfile = fopen('temp.'.$extension, 'wb');
        fwrite($tempfile, $photo);
        fclose($tempfile);
        return 'temp.'.$extension;
    }

    public static function purgeTemp($filename) {
        unlink($filename);
    }
}