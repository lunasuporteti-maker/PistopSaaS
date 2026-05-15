<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if ($user->estaBloqueado()) {
            return response()->json([
                'message' => 'Conta bloqueada temporariamente. Tente novamente mais tarde.',
            ], 429);
        }

        if (! Hash::check($request->password, $user->password)) {
            $user->registrarTentativaFalha();
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if (! $user->ativo) {
            return response()->json(['message' => 'Usuário desativado.'], 403);
        }

        $user->resetarBloqueio();

        // Revoga tokens do mesmo device para manter sessão única por dispositivo
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'perfil' => $user->perfil,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function alterarSenha(Request $request)
    {
        $request->validate([
            'senha_atual' => 'required',
            'nova_senha'  => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->senha_atual, $user->password)) {
            return response()->json(['message' => 'Senha atual incorreta.'], 422);
        }

        $user->update(['password' => Hash::make($request->nova_senha)]);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }
}
