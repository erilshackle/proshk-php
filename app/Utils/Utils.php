<?php


namespace App\Utils;

class Utils {

    public static function escape($var) {
        return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
    }

    // create here your public static utils functions 

}