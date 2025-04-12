<?php

final class App
{
    private $config = [];
    private static $env = DEFAULT_ENV;

    public function __construct(array $config = [])
    {
        $this->config = $this->loadIni();
        $this->config = array_merge($this->config, $config);
        $this->setEnvironment(getenv('APP_ENV') ?? DEFAULT_ENV);
        $this->applyAppInitializtions();
    }

    /**
     * Get Preferences values from site.ini
     */
    public function get($key, $default = null)
    {
        $key = strtolower($key);
        if (str_contains($key, '.')) {
            [$section, $subkey] = explode('.', $key, 2);
            return $this->config[$section][$subkey] ?? $default;
        }
        return $this->config[$key] ?? $default;
    }

    private function loadIni()
    {
        if (file_exists($ini = ROOT . '/site.ini')) {
            return array_change_key_case(parse_ini_file($ini, true), CASE_LOWER);
        }
    }

    protected function setEnvironment($APP_MODE)
    {
        self::$env = $APP_MODE;
        $debug = getenv("APP_DEBUG") ?? (!in_array(self::$env, ['development', 'local', 'dev', ENV_DEVELOPMENT]));

        if (self::$env == ENV_PRODUCTION) {
            error_reporting(0);
            ini_set('display_errors', '0');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', $debug ? '1' : '0');
        }
    }


    private function applyAppInitializtions()
    {
        $ini = $this->config;
        // set timezone
        date_default_timezone_set($this->get('app.timezone') ?? 'UTC');
        // date timezone

        // set language
        setlocale(LC_ALL, $this->get('app.locale') ?? 'en_US.UTF-8');
        // set charset
        mb_internal_encoding($this->get('app.charset') ?? 'UTF-8');
        // set max execution time
        ini_set('max_execution_time', $this->get('app.max_execution_time') ?? '30');
        // set max input vars
        ini_set('max_input_vars', $this->get('app.max_input_vars') ?? '1000');
        // set max post size
        ini_set('post_max_size', $this->get('app.post_max_size') ?? '20M');
    }
}
