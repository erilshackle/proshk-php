<?php

function _view($view, $data = [])
{
    global $app;
    $file = PATH_VIEWS . '/' . str_replace('.', '/', $view) . '.php';
    if (!file_exists($file)) {
        throw new Exception('View file not found: ' . $view);
    }
    ob_start();
    extract($data);
    include $file;
    return ob_get_clean();
}

function viewErrorPage(int $code, $data = [])
{
    try {
        http_response_code($code);
        return _view('_errors/' . $code, $data);
    } catch (Exception $e) {
        return null;
    }
}




function viewException(Throwable $exception): void
{
    $file = $exception->getFile();
    $line = $exception->getLine();
    $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    $trace = $exception->getTraceAsString();
    $code_boundaries = 2;
    $class = get_class($exception);
    $fileContent = file($file);
    $startLine = max($line - $code_boundaries - 1, 0);
    $endLine = min($line + $code_boundaries, count($fileContent));

    $codeSnippet = '';
    for ($i = $startLine; $i < $endLine; $i++) {
        $highlight = ($i + 1 === $line) ? 'style="background:#ffcccc;font-weight:bold;"' : '';
        $codeSnippet .= sprintf(
            '<tr %s><td style="color:gray;padding-right:10px;">%d</td><td>%s</td></tr>',
            $highlight,
            $i + 1,
            htmlspecialchars($fileContent[$i], ENT_QUOTES, 'UTF-8')
        );
    }
    ob_end_clean();
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{font-family:Arial,sans-serif;background:#f8f9fa;color:#333;margin:0;padding:20px;}
        .container{max-width:900px;margin:20px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
        .openfile{text-decoration:none;opacity:50%;}
        h1{color:#dc3545;} .error-details{margin:20px 0;padding:10px;background:#ffecec;border-left:5px solid #dc3545;}
        pre{background:#272822;color:#f8f8f2;padding:15px;border-radius:5px;overflow-x:auto;white-space:pre-wrap;}
        table{width:100%;border-collapse:collapse;margin-top:10px;background:#f9f9f9;}
        td{padding:5px;font-family:monospace;}
    </style>
</head>
<body>
    <div class="container">
        <h1>$class</h1>
        <div class="error-details">
            <code><strong><sup>Message:</sup>
            <br></strong></code>$message<br>
        </div>
        <details>
            <summary>
                <code><strong>File: </strong>$file ($line)</code>
            </summary>
            <table>$codeSnippet</table>
        </details>
        <h2>Stack Trace</h2>
        <pre>$trace</pre>
    </div>
</body>
</html>
HTML;
    exit;
}

