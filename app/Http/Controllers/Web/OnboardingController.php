<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Funcionario;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    private function getProgress(): array
    {
        $raw = Configuracao::get('onboarding_progress', '');
        $progress = $raw ? json_decode($raw, true) : [];

        return array_merge([
            'welcome_seen'    => false,
            'branding_done'   => false,
            'employee_done'   => false,
            'catalog_done'    => false,
            'first_os_done'   => false,
            'wizard_concluido' => false,
        ], $progress ?? []);
    }

    private function saveProgress(array $progress): void
    {
        // first_os_done é calculado dinamicamente — não armazenar falso se já existe OS
        if (! $progress['first_os_done']) {
            $progress['first_os_done'] = Orcamento::exists();
        }

        Configuracao::set('onboarding_progress', json_encode($progress));
    }

    public function wizard()
    {
        $progress = $this->getProgress();
        $tenant   = app('tenant');

        return view('pitstop.onboarding.wizard', compact('progress', 'tenant'));
    }

    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'step' => 'required|in:welcome_seen,branding_done,employee_done,catalog_done,wizard_concluido',
        ]);

        $progress = $this->getProgress();
        $progress[$validated['step']] = true;

        if ($validated['step'] === 'catalog_done') {
            $progress['wizard_concluido'] = false; // não fechar ainda
        }

        $this->saveProgress($progress);

        return response()->json(['ok' => true, 'progress' => $progress]);
    }

    public function skip()
    {
        // "Fazer depois" — esconde só na sessão atual, não marca como concluído permanentemente
        session(['wizard_adiado' => true]);

        return response()->json(['ok' => true]);
    }

    public function concluir()
    {
        // Chamado ao finalizar o passo 5 — marca como concluído permanentemente
        $progress = $this->getProgress();
        $progress['wizard_concluido'] = true;
        $this->saveProgress($progress);

        return response()->json(['ok' => true]);
    }

    public function saveBranding(Request $request)
    {
        $validated = $request->validate([
            'nome_oficina' => 'nullable|string|max:100',
            'logradouro'   => 'nullable|string|max:150',
            'numero'       => 'nullable|string|max:20',
            'bairro'       => 'nullable|string|max:80',
            'cidade'       => 'nullable|string|max:80',
            'uf'           => 'nullable|string|size:2',
            'cep'          => 'nullable|string|max:10',
            'logo'         => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        $tenant = app('tenant');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("tenants/{$tenant->slug}", 'public');
            Configuracao::set('logo_path', Storage::url($path));
        }

        if (! empty($validated['nome_oficina'])) {
            $tenant->update(['nome' => $validated['nome_oficina']]);
        }

        foreach (['logradouro', 'numero', 'bairro', 'cidade', 'uf', 'cep'] as $campo) {
            if (! empty($validated[$campo])) {
                Configuracao::set($campo, $validated[$campo]);
            }
        }

        $progress = $this->getProgress();
        $progress['branding_done'] = true;
        $this->saveProgress($progress);

        return response()->json(['ok' => true]);
    }

    public function saveEmployee(Request $request)
    {
        $validated = $request->validate([
            'nome'  => 'required|string|max:100',
            'cargo' => 'nullable|string|max:80',
        ]);

        $tenant = app('tenant');

        Funcionario::create([
            'tenant_id' => $tenant->id,
            'nome'      => $validated['nome'],
            'cargo'     => $validated['cargo'] ?? 'Mecânico',
            'ativo'     => true,
        ]);

        $progress = $this->getProgress();
        $progress['employee_done'] = true;
        $this->saveProgress($progress);

        return response()->json(['ok' => true]);
    }
}
