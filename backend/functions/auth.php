<?php


function auth_login($email, $password)
{
    $user = Auth::login(['email' => $email, 'password' => $password]);
    if ($user) {
        Session::set('user', $user);
        return true;
    } else {
        return false;
    }
}

function auth_check($role = null)
{
    if (Session::has('user')) {
        $user = Session::get('user');
        if ($role) {
            return $user->role == $role;
        }
        return true;
    }
    return false;
}

function auth_validate($role = null)
{
    if (!auth_check($role)) {
        redirect('/login');
    }
    return true;
}

function auth_logout()
{
    Session::destroy();
    redirect('/login');
}