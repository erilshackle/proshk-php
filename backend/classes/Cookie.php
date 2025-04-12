<?php

namespace classes;

class Cookie
{
    // Definir um cookie
    public static function set($name, $value, $expiry = 3600, $path = "/", $secure = false, $httponly = false)
    {
        // Calcular o tempo de expiração
        $expiryTime = time() + $expiry;

        // Definir o cookie
        setcookie($name, $value, $expiryTime, $path, "", $secure, $httponly);
    }

    // Obter o valor de um cookie
    public static function get($name)
    {
        // Verifica se o cookie existe
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return null;
    }

    // Excluir um cookie
    public static function delete($name, $path = "/")
    {
        // Para excluir, basta definir o cookie com uma data de expiração no passado
        setcookie($name, "", time() - 3600, $path);
        unset($_COOKIE[$name]); // Remove o cookie do array $_COOKIE
    }

    // Verificar se o cookie existe
    public static function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
}
