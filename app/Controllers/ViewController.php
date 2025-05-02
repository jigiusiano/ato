<?php

namespace App\Controllers;

class ViewController extends BaseController
{
    public function login(): string
    {
        return view('login');
    }

    public function workspace(): string
    {
        return view('workspace');
    }

    public function profile(): string
    {
        return view('profile');
    }

    public function register(): string
    {
        return view('register');
    }
}
