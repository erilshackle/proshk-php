<?php

class Auth
{
    private static $sessionKey = 'user_auth';
    private static $rememberMeKey = 'remember_token';
    private static DB $db;
    private static array $entries = ['email', 'username']; // Campos permitidos para login
    private static int $rememberDays = 30; // Dias para lembrar o usuário
    private static string $authTable = 'users'; // Tabela de autenticação

    // Inicia a sessão caso não tenha sido iniciada
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setConfig(array $config): void
    {
        if (isset($config['entries']) && is_array($config['entries'])) {
            self::$entries = $config['entries'];
        }
        if (isset($config['rememberDays']) && is_int($config['rememberDays'])) {
            self::$rememberDays = $config['rememberDays'];
        }
        if (isset($config['authTable']) && is_string($config['authTable'])) {
            self::$authTable = $config['authTable'];
        }
    }

    public static function login(array $credentials, bool $rememberMe = false): bool
    {
        foreach (self::$entries as $field) {
            if (isset($credentials[$field])) {
                return self::loginBy($field, $credentials[$field], $credentials['password'] ?? '', $rememberMe);
            }
        }
        return false;
    }

    public static function loginBy(string $field, string $value, string $password, bool $rememberMe = false): bool
    {
        self::startSession();
        self::$db = DB::getInstance();
        $user = self::$db->query("SELECT * FROM " . self::$authTable . " WHERE $field = ?", [$value]);
        if ($user && password_verify($password, $user[0]['password'])) {
            $_SESSION[self::$sessionKey] = $user[0];

            if ($rememberMe) {
                self::setRememberMe();
            }

            return true;
        }
        return false;
    }

    public static function setRememberMe(): void
    {
        if (self::check()) {
            $user = self::userId();
            $token = bin2hex(random_bytes(32));
            setcookie(self::$rememberMeKey, $token, time() + (86400 * self::$rememberDays), "/", "", false, true);
            self::$db->execute("UPDATE " . self::$authTable . " SET remember_token = ? WHERE id = ?", [$token, $user['id']]);
        }
    }

    public static function logout(): void
    {
        self::startSession();  // Garantir que a sessão está iniciada
        unset($_SESSION[self::$sessionKey]);
        setcookie(self::$rememberMeKey, '', time() - 3600, "/", "", false, true);
    }

    public static function check(): bool
    {
        self::startSession();  // Garantir que a sessão está iniciada
        return isset($_SESSION[self::$sessionKey]);
    }

    public static function userId(): ?array
    {
        self::startSession();  // Garantir que a sessão está iniciada
        return $_SESSION[self::$sessionKey] ?? null;
    }

    public static function validate(): void
    {
        if (!self::check()) {
            header("Location: /login");
            exit();
        }
    }
}
