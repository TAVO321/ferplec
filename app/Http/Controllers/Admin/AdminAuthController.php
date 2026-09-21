<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function mostrarLogin(): Response
    {
        return Inertia::render('Admin/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Escribe tu correo.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        if (! Auth::attempt($datos, (bool) $request->boolean('recuerdame'))) {
            return back()->withErrors(['email' => 'Correo o contraseña incorrectos.']);
        }

        $user = $request->user();

        if (! $user->activo) {
            Auth::logout();

            return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        if (! $user->esAdministrador()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Este acceso es solo para el administrador.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}