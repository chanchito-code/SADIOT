<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerificationCodeMail;

class RegisterController extends Controller
{
    /** Paso 1: mostrar formulario de email */
    public function showEmailForm()
    {
        return view('auth.register-email');
    }

    /** Paso 2: recibir email, generar y enviar código */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        // Generar código aleatorio de 6 dígitos
        $code = rand(100000, 999999);

        // Guardar en sesión
        Session::put('register_email', $request->email);
        Session::put('register_code', $code);

        // Enviar email con el código
        Mail::to($request->email)->send(new VerificationCodeMail($code));

        return redirect()->route('register.verifyCode')
                         ->with('message', 'Código enviado a ' . $request->email);
    }

    /** Paso 3: mostrar formulario para ingresar el código */
    public function showVerifyCodeForm()
    {
        if (!Session::has('register_email')) {
            return redirect()->route('register');
        }
        return view('auth.verify-code');
    }

    /** Paso 4: validar el código ingresado */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        if ((string) Session::get('register_code') === $request->code) {
            return redirect()->route('register.profile');
        }

        return back()
            ->withErrors(['code' => 'Código incorrecto'])
            ->withInput();
    }

    /** Paso 5: mostrar formulario de perfil (usuario + fecha + password) */
    public function showProfileForm()
    {
        if (!Session::has('register_email')) {
            return redirect()->route('register');
        }
        return view('auth.register-profile');
    }

    /** Paso 6: guardar usuario y finalizar registro */
    public function saveProfile(Request $request)
    {
        $request->validate([
            'username'       => 'required|string|min:3|max:100|unique:users,username',
            'birthdate'      => 'required|date',
            'password'       => 'required|string|min:8|confirmed',
        ]);

        $email = Session::get('register_email');

        $user = new User();
        $user->email = $email;
        $user->username = $request->username;
        $user->birthdate = $request->birthdate;
        $user->password = Hash::make($request->password);
        $user->save();

        // Limpiar sesión
        Session::forget('register_email');
        Session::forget('register_code');

        // Loguear al usuario recién creado
        auth()->login($user);

        // Diagnóstico: comprobar si el login fue exitoso
        //dd(
        //    'Auth check:', auth()->check(),
        //    'User:', auth()->user()
        //);

        // Si llegas aquí, redirige al dashboard
        return redirect()->route('dashboard');
    }
}
