{{--
    Wizard de onboarding — overlay full-screen Bootstrap 4.
    Incluído automaticamente pelo layout enquanto wizard_concluido != true.
--}}
@php
    $progRaw  = \App\Models\Configuracao::get('onboarding_progress', '');
    $prog     = $progRaw ? (json_decode($progRaw, true) ?? []) : [];
    $logoPath = \App\Models\Configuracao::get('logo_path', '');
    $tenant   = app('tenant');
    $ufs      = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
@endphp

<style>
.wz-overlay{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.65);display:flex;align-items:center;justify-content:center;padding:12px;}
.wz-card{background:#fff;border-radius:12px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;display:flex;flex-direction:column;}
[data-theme="dark"] .wz-card{background:#1e1e2e;color:#e2e8f0;}
.wz-header{padding:24px 24px 0;display:flex;justify-content:space-between;align-items:center;}
.wz-header h2{font-size:1.1rem;font-weight:700;margin:0;}
.wz-stepper{display:flex;gap:4px;padding:16px 24px 0;}
.wz-step-dot{flex:1;height:4px;border-radius:2px;background:#e2e8f0;transition:background .3s;}
.wz-step-dot.active{background:#2563eb;}
.wz-body{padding:24px;flex:1;}
.wz-body h3{font-size:1rem;font-weight:600;margin-bottom:8px;}
.wz-body p{font-size:.875rem;color:#64748b;margin-bottom:16px;}
[data-theme="dark"] .wz-body p{color:#94a3b8;}
.wz-footer{padding:16px 24px 24px;display:flex;justify-content:space-between;align-items:center;gap:8px;border-top:1px solid #f1f5f9;}
[data-theme="dark"] .wz-footer{border-top-color:#2d2d3f;}
.wz-step{display:none;}
.wz-step.show{display:block;}
@media(max-width:400px){.wz-footer{flex-wrap:wrap;}.wz-footer .btn-skip-all{order:3;width:100%;text-align:center;}}
</style>

<div class="wz-overlay" id="wzOverlay">
  <div class="wz-card" role="dialog" aria-modal="true" aria-label="Configuração inicial da sua oficina">

    {{-- Header --}}
    <div class="wz-header">
      <h2 id="wzTitle">Bem-vindo ao PitStop! 🎉</h2>
      <span style="font-size:.75rem;color:#94a3b8;" id="wzStepCount">Passo 1 de 5</span>
    </div>

    {{-- Stepper --}}
    <div class="wz-stepper">
      @for($i = 1; $i <= 5; $i++)
        <div class="wz-step-dot" id="dot{{ $i }}"></div>
      @endfor
    </div>

    {{-- ── Passo 1: Boas-vindas ──────────────────────────────── --}}
    <div class="wz-body wz-step show" data-step="1">
      <h3>Sua conta está pronta! 🚀</h3>
      <p>Em poucos passos você vai configurar sua oficina e já poderá começar a usar o PitStop. Leva menos de 3 minutos.</p>
      <ul style="font-size:.875rem;line-height:2;">
        <li>✅ Personalizar o nome e logo da sua oficina</li>
        <li>✅ Adicionar seu primeiro funcionário</li>
        <li>✅ Importar ou conferir o catálogo de peças</li>
        <li>✅ Ver os próximos passos</li>
      </ul>
    </div>

    {{-- ── Passo 2: Branding ─────────────────────────────────── --}}
    <div class="wz-body wz-step" data-step="2">
      <h3>Personalize sua oficina</h3>
      <p>Adicione o logo e o endereço que aparecerão nos PDFs de orçamento e OS.</p>
      <form id="formBranding" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label style="font-size:.8rem;font-weight:600;">Nome da oficina</label>
          <input type="text" name="nome_oficina" class="form-control form-control-sm"
                 value="{{ $tenant->nome }}" maxlength="100">
        </div>
        <div class="form-group">
          <label style="font-size:.8rem;font-weight:600;">Logo (PNG/JPG, máx 2MB)</label>
          @if($logoPath)
            <div class="mb-1"><img src="{{ $logoPath }}" style="height:40px;border-radius:4px;" alt="Logo atual"></div>
          @endif
          <input type="file" name="logo" class="form-control-file" accept="image/png,image/jpeg">
        </div>
        <div class="form-row">
          <div class="form-group col-8">
            <label style="font-size:.8rem;font-weight:600;">Logradouro</label>
            <input type="text" name="logradouro" class="form-control form-control-sm" maxlength="150">
          </div>
          <div class="form-group col-4">
            <label style="font-size:.8rem;font-weight:600;">Número</label>
            <input type="text" name="numero" class="form-control form-control-sm" maxlength="20">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-6">
            <label style="font-size:.8rem;font-weight:600;">Bairro</label>
            <input type="text" name="bairro" class="form-control form-control-sm" maxlength="80">
          </div>
          <div class="form-group col-4">
            <label style="font-size:.8rem;font-weight:600;">Cidade</label>
            <input type="text" name="cidade" class="form-control form-control-sm" maxlength="80">
          </div>
          <div class="form-group col-2">
            <label style="font-size:.8rem;font-weight:600;">UF</label>
            <select name="uf" class="form-control form-control-sm">
              <option value="">—</option>
              @foreach($ufs as $uf)<option value="{{ $uf }}">{{ $uf }}</option>@endforeach
            </select>
          </div>
        </div>
      </form>
    </div>

    {{-- ── Passo 3: Funcionário ──────────────────────────────── --}}
    <div class="wz-body wz-step" data-step="3">
      <h3>Adicione um funcionário</h3>
      <p>Cadastre quem vai usar o PitStop. Você pode adicionar mais depois.</p>
      <form id="formEmployee">
        @csrf
        <div class="form-group">
          <label style="font-size:.8rem;font-weight:600;">Nome completo <span class="text-danger">*</span></label>
          <input type="text" name="nome" class="form-control form-control-sm" maxlength="100" required>
        </div>
        <div class="form-group">
          <label style="font-size:.8rem;font-weight:600;">Cargo</label>
          <input type="text" name="cargo" class="form-control form-control-sm" maxlength="80" placeholder="Mecânico">
        </div>
      </form>
    </div>

    {{-- ── Passo 4: Catálogo de peças ────────────────────────── --}}
    <div class="wz-body wz-step" data-step="4">
      <h3>Catálogo de peças</h3>
      <p>Já adicionamos 5 peças de exemplo para você. Você pode editar, remover ou importar suas próprias peças via CSV depois.</p>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;font-size:.8rem;">
        <strong>✅ Peças de exemplo já cadastradas:</strong>
        <ul style="margin:6px 0 0 16px;">
          <li>Óleo Motor 5W30</li>
          <li>Filtro de Óleo</li>
          <li>Filtro de Ar</li>
          <li>Vela de Ignição</li>
          <li>Pastilha de Freio Dianteira</li>
        </ul>
      </div>
      <p class="mt-3" style="font-size:.8rem;">Para importar seu próprio catálogo, acesse <strong>Peças → Importar CSV</strong> depois de concluir o wizard.</p>
    </div>

    {{-- ── Passo 5: Próximos passos ─────────────────────────── --}}
    <div class="wz-body wz-step" data-step="5">
      <h3>Você está pronto! 🎊</h3>
      <p>Confira o que fazer agora:</p>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:.875rem;">
        <a href="{{ route('orcamentos.create') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:inherit;">
          <span style="font-size:1.2rem;">📋</span>
          <div><strong>Criar primeiro orçamento</strong><br><small style="color:#64748b;">Registre um serviço para um cliente</small></div>
        </a>
        <a href="{{ route('clientes.create') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:inherit;">
          <span style="font-size:1.2rem;">👤</span>
          <div><strong>Cadastrar cliente</strong><br><small style="color:#64748b;">Adicione dados dos seus clientes</small></div>
        </a>
        <a href="{{ route('kanban') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:inherit;">
          <span style="font-size:1.2rem;">🔧</span>
          <div><strong>Ver o Kanban</strong><br><small style="color:#64748b;">Acompanhe o status dos serviços</small></div>
        </a>
      </div>
    </div>

    {{-- Footer com botões --}}
    <div class="wz-footer">
      <button type="button" class="btn btn-link btn-sm btn-skip-all text-muted p-0"
              onclick="wzSkipAll()" id="wzBtnSkipAll">
        Fazer isso depois
      </button>
      <div style="display:flex;gap:8px;align-items:center;">
        <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="wzSkipStep()" id="wzBtnSkip" style="display:none;">
          Pular este passo
        </button>
        <button type="button" class="btn btn-primary btn-sm"
                onclick="wzNext()" id="wzBtnNext">
          Começar →
        </button>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
    var current = 1;
    var total   = 5;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    var titles = [
        'Bem-vindo ao PitStop! 🎉',
        'Personalize sua oficina',
        'Adicione um funcionário',
        'Catálogo de peças',
        'Próximos passos'
    ];

    // Passos que têm "Pular este passo" (2-5)
    var skippable = [2, 3, 4, 5];

    function render() {
        document.querySelectorAll('.wz-step').forEach(function (el) {
            el.classList.toggle('show', parseInt(el.dataset.step) === current);
        });
        for (var i = 1; i <= total; i++) {
            document.getElementById('dot' + i).classList.toggle('active', i <= current);
        }
        document.getElementById('wzTitle').textContent = titles[current - 1];
        document.getElementById('wzStepCount').textContent = 'Passo ' + current + ' de ' + total;
        document.getElementById('wzBtnSkip').style.display = skippable.includes(current) ? '' : 'none';
        document.getElementById('wzBtnNext').textContent = current === total ? 'Concluir ✓' : (current === 1 ? 'Começar →' : 'Continuar →');
    }

    function post(url, data, isForm) {
        var opts = {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        };
        if (isForm) {
            data.append('_method', 'POST');
            opts.body = data;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(data);
        }
        return fetch(url, opts);
    }

    function put(url, data) {
        return fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
    }

    function markStep(step) {
        return put('{{ route("onboarding.progress") }}', { step: step });
    }

    window.wzNext = function () {
        if (current === 1) {
            markStep('welcome_seen').then(function () { goTo(2); });
        } else if (current === 2) {
            var form = document.getElementById('formBranding');
            var fd = new FormData(form);
            post('{{ route("onboarding.branding") }}', fd, true)
                .then(function (r) { return r.json(); })
                .then(function () { goTo(3); });
        } else if (current === 3) {
            var form = document.getElementById('formEmployee');
            var nome = form.querySelector('[name=nome]').value.trim();
            if (!nome) { form.querySelector('[name=nome]').focus(); return; }
            var fd = new FormData(form);
            post('{{ route("onboarding.employee") }}', fd, true)
                .then(function (r) { return r.json(); })
                .then(function () { goTo(4); });
        } else if (current === 4) {
            markStep('catalog_done').then(function () { goTo(5); });
        } else if (current === 5) {
            wzFinish();
        }
    };

    window.wzSkipStep = function () {
        goTo(current + 1);
    };

    window.wzSkipAll = function () {
        fetch('{{ route("onboarding.skip") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        }).then(function () {
            document.getElementById('wzOverlay').remove();
        });
    };

    function wzFinish() {
        // Concluir permanentemente — só chamado ao finalizar o passo 5
        fetch('{{ route("onboarding.concluir") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        }).then(function () {
            document.getElementById('wzOverlay').remove();
            var badge = document.querySelector('[href="{{ route("onboarding.wizard") }}"]');
            if (badge) badge.remove();
        });
    }

    function goTo(step) {
        current = Math.min(step, total);
        render();
    }

    render();
}());
</script>
