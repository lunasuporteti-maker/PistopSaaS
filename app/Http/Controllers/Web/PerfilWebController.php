<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PerfilWebController extends Controller
{
    public function edit()
    {
        return view('pitstop.perfil.edit', ['usuario' => auth()->user()]);
    }

    public function updateDados(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'    => 'nullable|email|max:100|unique:users,email,' . $user->id,
        ], [
            'username.unique' => 'Este login já está em uso.',
            'email.unique'    => 'Este e-mail já está em uso.',
        ]);

        $user->update($data);
        return back()->with('success', 'Dados atualizados com sucesso!');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'senha_atual'          => ['required', 'string'],
            'password'             => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'password.min'         => 'A nova senha deve ter no mínimo 8 caracteres.',
            'password.mixed_case'  => 'A senha deve ter letras maiúsculas e minúsculas.',
            'password.numbers'     => 'A senha deve ter ao menos um número.',
            'password.symbols'     => 'A senha deve ter ao menos um caractere especial.',
            'password.confirmed'   => 'A confirmação de senha não confere.',
        ]);

        if (! Hash::check($request->senha_atual, $user->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
