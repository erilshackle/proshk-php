<?php

function response(?string $content, int $status = 200, ?string $type = null)
{
    http_response_code($status);
    if ($type) {
        header("Content-Type: $type");
    }
    echo $content;
    exit;
}


function response_json(array $data, int $status = 200)
{
    header("Content-Type: application/json");
    return response(json_encode($data, JSON_PRETTY_PRINT), $status);
}


function response_blob(string $file, int $status = 200)
{
    header("Content-Type: application/octet-stream");
    return response(file_get_contents($file), $status);
}


function redirect(?string $url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function postRedirectGet()
{
    redirect($_SERVER['HTTP_REFERER']);
}


function flash(string $key, $value = null)
{
    if ($value !== null) {
        $_SESSION['__flash'][$key] = $value;
    } elseif (isset($_SESSION['__flash'][$key])) {
        $flashed = $_SESSION['__flash'][$key];
        unset($_SESSION['__flash'][$key]);
    }
    return $flashed;
}

function unflash()
{
    if (isset($_SESSION['__flash'])) {
        unset($_SESSION['__flash']);
    }
}
