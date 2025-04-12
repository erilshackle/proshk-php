<?php

/**
 * 
 * @param mixed $layout
 * @param array $data
 * @throws \Exception
 * @return void
 */
function page_layouts_extends($layout, array $data = [])
{
    static $extended;
    $layout = str_replace(".php", '', $layout);

    // Layuot Error
    if ($extended) {
        throw new Exception("O layout `{$layout}` foi extendido mais de uma vez!");
    } elseif (!file_exists($layoutPath = PATH_LAYOUTS . "/{$layout}.php")) {
        throw new ErrorException("Erro: O layout `{$layout}` não foi encontrado!");
    }

    ob_start();
    global $app;
    register_shutdown_function(function () use ($layoutPath, &$data, &$app) {
        $content = ob_get_clean();

        if (!$content || empty($content)) {
            response(null, 404);
        }

        $data['content'] = $content;
        $data['app'] = $app;

        function processDirectives($content, &$data)
        {
            // Substitui @{{content}}
            $content = preg_replace('/@\{\{\s*content\s*\}\}/i', '{{content}}', $content);

            // Substitui @layout('var')
            $content = preg_replace_callback('/@layout\([\'\"]([a-zA-Z_][a-zA-Z0-9_]*)[\'\"]\)/i', function ($matches) use ($data) {
                $var = trim($matches[1]);
                return $data[$var] ?? '';
            }, $content);

            return $content;
        }

        ob_start();
        extract($data);
        include($layoutPath);
        $layoutContent = ob_get_clean();
        $output = processDirectives($layoutContent, $data);
        echo $output;
    });
    $extended = true;
}

/**
 * Herda um layout baseado no Header e Footer. os arquivos devem terminar com .header.php e .footer.php
 * @param string $header_footer a file name in @layouts/ that ends with .header.php and .footer.php (ex: main)
 * @return void
 */
function page_layout_include(string $header_footer, array $data = [])
{
    global $app;
    ob_clean();
    extract($data);
    $header_footer = str_replace(['.php', '.header', '.footer'], [''], $header_footer);
    $folderPath = PATH_LAYOUTS . "/$header_footer";
    if (file_exists($header =  "$folderPath.header.php")) {
        include_once $header;
    }
    // Registra a função para incluir o footer ao final da execução, se existir
    if (file_exists($footer =  "$folderPath.footer.php")) {
        register_shutdown_function(function () use ($footer) {
            include $footer;
        });
    }
}
