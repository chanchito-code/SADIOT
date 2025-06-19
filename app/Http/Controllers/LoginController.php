<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Ya no hay vista de login independiente: redirigimos a “/”
        return redirect('about');
    }

    public function login(Request $request)
    {
        // Validamos como antes
        $credentials = $request->only('login', 'password');

        if (Auth::attempt(['email' => $credentials['login'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Si falla, redirigimos a “/” con los errores para que el modal se abra
        return redirect('/')
            ->withErrors(['login' => 'Credenciales inválidas'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
