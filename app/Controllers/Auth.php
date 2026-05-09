<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AntrianModel;

class Auth extends BaseController
{
    public function login(): string
    {
        helper(['url', 'form']);

        return view('auth/login');
    }

    public function prosesLogin()
    {
        helper(['url', 'form']);

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())
            ->where('username', $username)
            ->first();

        if (! $user) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', ['Username tidak ditemukan']);
        }

        if (! password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', ['Password salah']);
        }

        session()->regenerate(true);
        session()->set([
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'role' => (string) $user['role'],
            'logged_in' => true,
            'isLoggedIn' => true,
        ]);

        // Cleanup otomatis agar DB lokal tidak penuh (aman: hanya data lama).
        try {
            (new AntrianModel())->cleanupOldRecords();
        } catch (\Throwable) {
            // ignore cleanup failures on login
        }

        return redirect()->to('/dashboard')
            ->with('success', 'Login berhasil, selamat datang!');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }
}

