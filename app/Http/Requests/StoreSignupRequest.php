<?php

namespace App\Http\Requests;

use App\Models\TenantSignup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validação do cadastro público de onboarding (PRD 03 — AC2, AC4, AC5).
 *
 * A checagem de e-mail é feita direto contra as TABELAS (users / tenant_signups)
 * via Rule::unique, o que ignora o global scope BelongsToTenant — necessário
 * porque o signup acontece antes do tenant existir e o e-mail é único global.
 */
class StoreSignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza entradas antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
            'slug_desejado' => is_string($this->slug_desejado) ? mb_strtolower(trim($this->slug_desejado)) : $this->slug_desejado,
            'uf' => is_string($this->uf) ? mb_strtoupper(trim($this->uf)) : $this->uf,
        ]);
    }

    public function rules(): array
    {
        $reservados = config('pitstop.slugs_reservados', []);

        return [
            'nome_oficina' => ['required', 'string', 'max:200'],

            // AC4: slug 3-30 chars, letras minúsculas/números/hífen,
            // não começa/termina com hífen, não pode ser reservado,
            // único em tenant_signups.slug_desejado E tenants.slug (CON-001).
            'slug_desejado' => [
                'required',
                'string',
                'regex:/^[a-z0-9]([a-z0-9-]{1,28})[a-z0-9]$/',
                Rule::notIn($reservados),
                Rule::unique('tenants', 'slug'),
                Rule::unique('tenant_signups', 'slug_desejado')
                    ->where(fn ($q) => $q->where('status', '!=', TenantSignup::STATUS_EXPIRED)),
            ],

            'cnpj' => ['nullable', 'string', 'max:18'],
            'telefone' => ['required', 'string', 'max:20'],
            'cidade' => ['required', 'string', 'max:120'],
            'uf' => ['required', 'string', 'size:2'],

            'nome_completo' => ['required', 'string', 'max:200'],

            // AC5: e-mail único cross-tenant em users + único entre signups ativos.
            'email' => [
                'required',
                'string',
                'email',
                'lowercase',
                'max:150',
                Rule::unique('users', 'email'),
                Rule::unique('tenant_signups', 'email')
                    ->where(fn ($q) => $q->where('status', '!=', TenantSignup::STATUS_EXPIRED)),
            ],

            // AC2: senha mín 8, ao menos 1 maiúscula e 1 número, confirmada.
            'senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'confirmed',
            ],

            // Termos + privacidade obrigatórios; marketing opcional.
            'aceite_termos' => ['required', 'accepted'],
            'consentimento_marketing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug_desejado.regex' => 'O endereço só pode ter letras minúsculas, números e hífen, e não pode começar nem terminar com hífen.',
            'slug_desejado.not_in' => 'Este endereço é reservado. Escolha outro.',
            'slug_desejado.unique' => 'Este endereço já está em uso. Escolha outro.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'senha.regex' => 'A senha deve conter ao menos uma letra maiúscula e um número.',
            'senha.confirmed' => 'A confirmação de senha não confere.',
            'aceite_termos.accepted' => 'Você precisa aceitar os Termos de Uso e a Política de Privacidade.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome_oficina' => 'nome da oficina',
            'slug_desejado' => 'endereço',
            'nome_completo' => 'nome completo',
            'aceite_termos' => 'termos de uso',
        ];
    }
}
