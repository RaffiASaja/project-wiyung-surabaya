<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login(): string
    {
        helper(['url', 'form']);

        return view('auth/login');
    }

    public function attemptLogin()
    {
        helper(['url', 'form']);

        $username = (string) ($this->request->getPost('username') ?? 'admin');

        session()->set([
            'isLoggedIn' => true,
            'username' => $username !== '' ? $username : 'admin',
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
