<?php
class Crypt {
    public function __construct($key) {
        $this->alg = 'AES-128-ECB';
        $this->key = $key;
    }

    public function encrypt($text){
        return openssl_encrypt($text, $this->alg, $this->key);
    }

    public function decrypt($text)
    {
        return openssl_decrypt($text, $this->alg, $this->key); 
    }
}