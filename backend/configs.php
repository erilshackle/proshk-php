<?php

// base
define("ROOT", dirname(__FILE__, 2));
define("HOST", dirname($_SERVER['SCRIPT_NAME']));

// paths
define("PATH_BACKEND", ROOT . "/backend");
define("PATH_PUBLIC", ROOT . "/public");
define("PATH_WEB", ROOT . "/web");
define("PATH_PAGES", ROOT . "/page");
define("PATH_STORAGE", ROOT . "/storage");
define("PATH_LAYOUTS", PATH_PAGES . "/layouts");
define("PATH_CACHE", PATH_STORAGE . "/cache");
define("PATH_LOGS", PATH_STORAGE . "/logs");
define("PATH_UPLOADS", PATH_STORAGE . "/uploads");

define("PATH_CLASSES", PATH_BACKEND . "/classes");
define("PATH_CONFIGS", PATH_BACKEND . "/configs");

// enviroment
define("ENV_DEVELOPMENT", 'development');
define("ENV_PRODUCTION", 'production');

// defaults (fallbacks)
define("DEFAULT_ENV", ENV_DEVELOPMENT);
define("DEFAULT_TIMEZONE", 'America/Sao_Paulo');
define("DEFAULT_LOCALE", 'pt-BR');
define("DEFAULT_CHARSET", 'UTF-8');
define("DEFAULT_LANGUAGE", 'pt-BR');
define("DEFAULT_CURRENCY", 'BRL');
define("DEFAULT_DATE_FORMAT", 'd/m/Y H:i:s');
define("DEFAULT_DATE_FORMAT_SHORT", 'd/m/Y');
define("DEFAULT_DATE_FORMAT_LONG", 'd \d\e F \d\e Y H:i:s');

// constants
const base_dir = ROOT;
const base_url = HOST . '/';
const storage = PATH_STORAGE . '/';

// initialize


// config class
final class Config
{
    private static $config = null;
    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$config === null) {
            self::$config = self::load('settings');
        }

        $configKey = strtolower($key);
        $configKeys = explode('.', $configKey);
        $configValue = self::$config;

        foreach ($configKeys as $configKeyPart) {
            if (is_array($configValue) && array_key_exists($configKeyPart, $configValue)) {
                $configValue = $configValue[$configKeyPart];
            } else {
                return $default;
            }
        }

        return $configValue ?? $default;
    }

    public static function load(string $cfgFile, bool $required = true): array
    {
        $cfgFile = str_replace('.php', '', $cfgFile);
        if (!file_exists($file = PATH_CONFIGS . "/$cfgFile.php")) {
            return $required ? (throw new Exception('Arquivo de configuração não encontrado: ' . $cfgFile)) : [];
        }
        return include $file;
    }
}
