<?php

function env($var, $default = null) {
    return $_ENV[$var] ?? $default;
}

function dd($vars)
{
    // <style>body{background: #282c34;}</style>
    echo '    
        <div style="
        background:#1a1d23; color: #61dafb;
        padding: 15px; border-radius: 5px;
        font-family: Consolas, monospace;
        font-size: 14px; line-height: 1.5;
        white-space: pre-wrap; overflow-x: auto;
        word-break: break-word; margin: 20px auto;
        max-width: 100%; max-width: 1000px;
    ">';
    $type = gettype($vars);
    echo '<strong>Debugging:</strong> ' . $type . '<br><br>';
    if (is_array($vars) || is_object($vars)) {
        print_r($vars);
    } else {
        var_dump($vars);
    }
    echo '</div>';
    exit;
}

function template($name, $data = []){
    extract($data);
    ob_start();
    include_once ROOT . "/{$name}.php";
    return ob_get_clean();
}