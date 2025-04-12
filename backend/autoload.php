<?php

require_once(dirname(__FILE__) . "/configs.php");
require_once(dirname(__FILE__) . "/helpers.php");

// load configs
foreach (['classes', 'functions'] as $dir) {
    $dir = PATH_BACKEND . "/$dir";
    if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $filename) {
            require_once $filename;
        }
    }
}

// auto loads
spl_autoload_register(function ($classname) {
    $class = ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
    if (file_exists($class)) {
        include_once $class;
    }
});


set_exception_handler(function (Throwable $exception) {
    if (ini_get('display_errors') !== '0') {
        viewException($exception);
    }
    throw $exception;
});



function loadDotEnv(?string $env = '')
{
    $env = str_replace([DEFAULT_ENV, '.env'], '', $env);
    $env = '/.env' . ($env ? ".$env" : '');
    if (!file_exists($envFile = ROOT . $env)) {
        throw new RuntimeException("Arquivo {$env} não encontrado!, APP_ENV mal configurado");
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignorar comentários

        list($key, $value) = explode('=', $line, 2) + [null, null];
        $key = trim($key);
        $value =  trim((trim($value)), '"\'');

        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }
};

