<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioWebController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin');

        $query = User::query();

        if ($busca = $request->get('busca')) {
            $query->where(function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('username', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        if ($perfil = $request->get('perfil')) {
            $query->where('perfil', $perfil);
        }

        $usuarios = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('pitstop.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize('gerente_ou_admin');
        $perfisDisponiveis = $this->perfisDisponiveis();
        return view('pitstop.usuarios.form', ['usuario' => new User, 'perfisDisponiveis' => $perfisDisponiveis]);
    }

    public function store(Request $request)
    {
        $this->authorize('gerente_ou_admin');

        $perfisPermitidos = $this->perfisDisponiveis();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email'    => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'perfil'   => ['required', 'in:' . implode(',', array_keys($perfisPermitidos))],
            'password' => 'required|min:6|confirmed',
        ], [
            'name.regex'       => 'O nome deve conter apenas letras e espaços.',
            'username.alpha_dash' => 'O login deve conter apenas letras, números, hífen ou underscore (sem espaços).',
        ]);

        User::create([
            'name'     => strtoupper($data['name']),
            'username' => strtolower($data['username']),
            'email'    => isset($data['email']) ? strtolower($data['email']) : null,
            'perfil'   => $data['perfil'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        $this->authorize('gerente_ou_admin');
        $perfisDisponiveis = $this->perfisDisponiveis();
        return view('pitstop.usuarios.form', compact('usuario', 'perfisDisponiveis'));
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorize('gerente_ou_admin');

        $perfisPermitidos = $this->perfisDisponiveis();

        // Gerente não pode editar admins
        if (auth()->user()->perfil === 'gerente' && $usuario->perfil === 'admin') {
            return back()->with('error', 'Você não tem permissão para editar um Administrador.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($usuario->id)],
            'email'    => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuario->id)],
            'perfil'   => ['required', 'in:' . implode(',', array_keys($perfisPermitidos))],
            'password' => 'nullable|min:6|confirmed',
            'ativo'    => 'boolean',
        ], [
            'name.regex'          => 'O nome deve conter apenas letras e espaços.',
            'username.alpha_dash' => 'O login deve conter apenas letras, números, hífen ou underscore (sem espaços).',
        ]);

        // Gerente não pode promover a admin
        if (auth()->user()->perfil === 'gerente' && ($data['perfil'] ?? '') === 'admin') {
            return back()->with('error', 'Você não pode atribuir o perfil de Administrador.');
        }

        // Não permite auto-alteração de perfil
        if ($usuario->id === auth()->id() && isset($data['perfil']) && $data['perfil'] !== $usuario->perfil) {
            return back()->with('error', 'Você não pode alterar o próprio perfil.');
        }

        $update = [
            'name'     => strtoupper($data['name']),
            'username' => strtolower($data['username']),
            'email'    => isset($data['email']) ? strtolower($data['email']) : null,
            'perfil'   => $data['perfil'],
            'ativo'    => $data['ativo'] ?? $usuario->ativo,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $usuario->update($update);
        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado.');
    }

    public function destroy(User $usuario)
    {
        $this->authorize('admin');

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído.');
    }

    public function desbloquear(User $usuario)
    {
        $this->authorize('gerente_ou_admin');
        $usuario->resetarBloqueio();
        return back()->with('success', "Usuário {$usuario->name} desbloqueado com sucesso.");
    }

    private function perfisDisponiveis(): array
    {
        $perfil = auth()->user()->perfil;

        if ($perfil === 'admin') {
            return [
                'operador' => 'Operador',
                'gerente'  => 'Gerente',
                'admin'    => 'Administrador',
            ];
        }

        return [
            'operador' => 'Operador',
            'gerente'  => 'Gerente',
        ];
    }
}
