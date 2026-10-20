@php
    $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criar conta | PitStop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    @if($recaptchaSiteKey)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    @endif

    <style>
        body { font-family:'Geist',sans-serif; background:#f4f5f7; color:#1a1a1a; }
        .signup-wrap { max-width:640px; margin:0 auto; padding:32px 16px; }
        .signup-card { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.06); padding:32px; }
        .brand { font-size:24px; font-weight:700; color:#111827; margin-bottom:4px; }
        .subtitle { color:#6b7280; font-size:14px; margin-bottom:24px; }
        .slug-prefix { background:#f3f4f6; border:1px solid #ced4da; border-right:0; border-radius:.25rem 0 0 .25rem; padding:.375rem .75rem; color:#6b7280; font-size:.875rem; }
        .slug-suffix { background:#f3f4f6; border:1px solid #ced4da; border-left:0; border-radius:0 .25rem .25rem 0; padding:.375rem .75rem; color:#6b7280; font-size:.875rem; }
        .slug-feedback { font-size:.8rem; margin-top:.25rem; min-height:1rem; }
        .slug-feedback.ok { color:#16a34a; }
        .slug-feedback.err { color:#dc2626; }
        .btn-pitstop { background:#2563eb; border:0; font-weight:600; padding:.6rem; }
        .btn-pitstop:hover { background:#1d4ed8; }
        .required::after { content:" *"; color:#dc2626; }
    </style>
</head>
<body>
<div class="signup-wrap">
    <div class="signup-card">
        <div class="brand">PitStop</div>
        <div class="subtitle">Crie sua conta e comece seu trial gratuito de 14 dias.</div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form id="signupForm" method="POST" action="{{ route('cadastro.store') }}" novalidate>
            @csrf
            <input type="hidden" name="recaptcha_token" id="recaptchaToken">

            <h6 class="text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.05em;">Dados da oficina</h6>

            <div class="form-group">
                <label for="nome_oficina" class="required">Nome da oficina</label>
                <input type="text" class="form-control" id="nome_oficina" name="nome_oficina" maxlength="200" value="{{ old('nome_oficina') }}" required>
            </div>

            <div class="form-group">
                <label for="slug_desejado" class="required">Endereço do seu PitStop</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="slug-prefix">app.iaqueatende.com.br/</span></div>
                    <input type="text" class="form-control" id="slug_desejado" name="slug_desejado" maxlength="30" value="{{ old('slug_desejado') }}" autocomplete="off" required>
                </div>
                <div class="slug-feedback" id="slugFeedback"></div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cnpj">CNPJ <small class="text-muted">(opcional)</small></label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" maxlength="18" value="{{ old('cnpj') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="telefone" class="required">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" maxlength="20" value="{{ old('telefone') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="cidade" class="required">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" maxlength="120" value="{{ old('cidade') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="uf" class="required">UF</label>
                    <select class="form-control" id="uf" name="uf" required>
                        <option value="">--</option>
                        @foreach($ufs as $uf)
                            <option value="{{ $uf }}" @selected(old('uf') === $uf)>{{ $uf }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="text-uppercase text-muted mb-3 mt-4" style="font-size:.7rem;letter-spacing:.05em;">Sua conta</h6>

            <div class="form-group">
                <label for="nome_completo" class="required">Nome completo</label>
                <input type="text" class="form-control" id="nome_completo" name="nome_completo" maxlength="200" value="{{ old('nome_completo') }}" required>
            </div>

            <div class="form-group">
                <label for="email" class="required">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150" value="{{ old('email') }}" required>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="senha" class="required">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                    <small class="text-muted">Mín. 8 caracteres, 1 maiúscula e 1 número.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="senha_confirmation" class="required">Confirmar senha</label>
                    <input type="password" class="form-control" id="senha_confirmation" name="senha_confirmation" required>
                </div>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="aceite_termos" name="aceite_termos" value="1" {{ old('aceite_termos') ? 'checked' : '' }} required>
                <label class="form-check-label" for="aceite_termos">
                    Li e aceito os <a href="https://pitstop.iaqueatende.com.br/termos" target="_blank">Termos de Uso</a> e a <a href="https://pitstop.iaqueatende.com.br/privacidade" target="_blank">Política de Privacidade</a>.
                </label>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="consentimento_marketing" name="consentimento_marketing" value="1" {{ old('consentimento_marketing') ? 'checked' : '' }}>
                <label class="form-check-label" for="consentimento_marketing">
                    Quero receber e-mails com novidades e dicas de produto (opcional).
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-pitstop" id="submitBtn">Criar minha conta</button>

            <p class="text-center text-muted mt-3" style="font-size:.8rem;">
                Já tem conta? <a href="/login">Entrar</a>
            </p>
        </form>
    </div>
</div>

<script>
(function () {
    var verificarSlugUrl = @json(route('cadastro.verificar-slug'));
    var recaptchaSiteKey = @json($recaptchaSiteKey);

    var nomeOficina = document.getElementById('nome_oficina');
    var slugInput   = document.getElementById('slug_desejado');
    var slugFeedback = document.getElementById('slugFeedback');
    var form        = document.getElementById('signupForm');
    var submitBtn   = document.getElementById('submitBtn');
    var tokenField  = document.getElementById('recaptchaToken');

    var slugTocadoManualmente = slugInput.value.length > 0;

    // T6.2 — auto-sugestão de slug a partir do nome (sem acentos, hifens).
    function slugify(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .slice(0, 30)
            .replace(/^-+|-+$/g, '');
    }

    nomeOficina.addEventListener('input', function () {
        if (!slugTocadoManualmente) {
            slugInput.value = slugify(nomeOficina.value);
            verificarSlug();
        }
    });

    slugInput.addEventListener('input', function () {
        slugTocadoManualmente = true;
        slugInput.value = slugInput.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
        verificarSlug();
    });

    // T6.1 — debounce 400ms.
    var debounceTimer = null;
    function verificarSlug() {
        clearTimeout(debounceTimer);
        var slug = slugInput.value;
        if (slug.length < 3) {
            slugFeedback.textContent = '';
            slugFeedback.className = 'slug-feedback';
            return;
        }
        debounceTimer = setTimeout(function () {
            fetch(verificarSlugUrl + '?slug=' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    slugFeedback.textContent = data.mensagem || '';
                    slugFeedback.className = 'slug-feedback ' + (data.status === 'disponivel' ? 'ok' : 'err');
                })
                .catch(function () {
                    slugFeedback.textContent = '';
                    slugFeedback.className = 'slug-feedback';
                });
        }, 400);
    }

    // T6.3 — reCAPTCHA v3 no submit.
    form.addEventListener('submit', function (e) {
        if (recaptchaSiteKey && window.grecaptcha) {
            e.preventDefault();
            submitBtn.disabled = true;
            grecaptcha.ready(function () {
                grecaptcha.execute(recaptchaSiteKey, { action: 'signup' }).then(function (token) {
                    tokenField.value = token;
                    form.submit();
                });
            });
        }
    });
}());
</script>
</body>
</html>
