<?php

final class Session
{
    // Inicia a sessão, se ainda não estiver iniciada
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Armazena um valor na sessão
    public static function set($key, $value)
    {
        self::start(); // Garante que a sessão esteja ativa
        $_SESSION[$key] = $value;
    }

    // Recupera um valor da sessão
    public static function get($key)
    {
        self::start(); // Garante que a sessão esteja ativa
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    // Verifica se um valor existe na sessão
    public static function has($key)
    {
        self::start(); // Garante que a sessão esteja ativa
        return isset($_SESSION[$key]);
    }

    // Remove um valor da sessão
    public static function remove($key)
    {
        self::start(); // Garante que a sessão esteja ativa
        unset($_SESSION[$key]);
    }

    // Destroi toda a sessão
    public static function destroy()
    {
        self::start(); // Garante que a sessão esteja ativa
        session_destroy();
    }


    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['__flash'][$key]);
    }

    public static function setFlash(string $key, $value)
    {
        self::start();
        $_SESSION['__flash'][$key] = $value;
    }
    
    public static function getFlash(string $key)
    {
        self::start();
        if(self::hasFlash($key)){
            $flash =  $_SESSION['__flash'][$key];
            unset($_SESSION['__flash'][$key]);
        }
        return $flash ?? null;
    }

}