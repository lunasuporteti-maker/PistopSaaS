<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminContaController extends Controller
{
    public function edit()
    {
        return view('admin.conta');
    }

    public function updateSenha(Request $request)
    {
        $request->validate([
            'senha_atual' => 'required|string',
            'senha_nova'  => 'required|string|min:8|confirmed',
        ], [
            'senha_nova.confirmed' => 'As senhas não coincidem.',
            'senha_nova.min'       => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->senha_atual, $user->password)) {
            return back()->withErrors(['senha_atual' => 'Senha atual incorreta.']);
        }

        $user->update(['password' => Hash::make($request->senha_nova)]);

        return back()->with('success', 'Senha alterada com sucesso.');
    }
}
