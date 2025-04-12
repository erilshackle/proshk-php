<?php

function bcrypt($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function csrfToken(?string $token = null): bool|string
{
    if ($token === null) {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
    } else {
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    return $token;
}

function csrf()
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">' . "\n";
}
