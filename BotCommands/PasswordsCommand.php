<?php

namespace BotCommands;

use Types\BotMessage;
use Types\Text;

class PasswordsCommand extends AbstactCommand
{
    public function __construct()
    {
        parent::__construct('hasla', 'Hasła do rzeczy');
    }

    public function execute(array $parameters) : BotMessage
    {
        if(count($parameters) > 1) {
            $function = array_shift($parameters);
            if($function === 'dodaj') {
                $key = array_shift($parameters);
                $valueLabel = implode(' ', $parameters);
                $exploded = explode('||||', $valueLabel);
                $value = $exploded[0];
                $label = $exploded[1];
                \DB::insertMetadata($key, $value, 'password', 1, $label);
                return new Text(utf8_encode('ok dodaje'));
            }

            if($function === 'usun') {
                $pattern = array_shift($parameters);
                \DB::deleteMetadataLike($pattern);
                return new Text('ok usuwam');
            }
        }
        elseif(count($parameters) === 1) {
            //podpowiedzi
            $function = array_shift($parameters);
            if($function === 'dodaj')
                return new Text(utf8_encode('dodaj [klucz wartosc||||etykieta]'));
            elseif ($function === 'usun')
                return new Text(utf8_encode('usun [klucz|etykieta]'));
            else
                return new Text(utf8_encode('Nieznana funkcja'));
        }

        $passwords = \DB::getMetaList('password');
        $x = '';
        foreach($passwords as $password)
        {
            $x .= $password['label'] .': ||'.$password['value'] .'|| '. PHP_EOL;
        }

        return new Text(str_replace(['.', '_'], ['\.', '\_'], utf8_encode($x)));
    }
}