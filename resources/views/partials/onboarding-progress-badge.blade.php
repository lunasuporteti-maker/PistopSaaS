@php
    $__prog = [];
    if (app()->bound('tenant') && auth()->check() && !auth()->user()->isSuperAdmin()) {
        $__raw  = \App\Models\Configuracao::get('onboarding_progress', '');
        $__prog = $__raw ? (json_decode($__raw, true) ?? []) : [];
    }
    $__done = !empty($__prog['wizard_concluido']);
    if (!$__done) {
        $__acoes = ['branding_done', 'employee_done', 'catalog_done', 'first_os_done'];
        $__count = count(array_filter($__acoes, fn($k) => !empty($__prog[$k])));
    }
@endphp
@if(!$__done && isset($__count))
<a href="{{ route('onboarding.wizard') }}"
   title="Configuração inicial"
   style="display:flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;background:var(--color-warning,#d97706);color:#fff;font-size:12px;font-weight:600;text-decoration:none;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
    {{ $__count }}/4
</a>
@endif
