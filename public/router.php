<?php

// Obtém a URL solicitada
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Verifica se o arquivo sem .php existe
$file_with_php = __DIR__ . $request_uri . ".php";

if (file_exists($file_with_php)) {
    // Se existir, redireciona para o arquivo correto
    include $file_with_php;
    exit;
}

// Se for um arquivo normal ou pasta, deixa o PHP embutido processar
if (file_exists(__DIR__ . $request_uri)) {
    return false;
}

// Se não encontrar nada, retorna um erro 404
http_response_code(404);
echo "404 - Página não encontrada";
