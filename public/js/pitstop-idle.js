/**
 * PitStop — Logout automático por inatividade
 * Avisa 60s antes de deslogar. Qualquer atividade reinicia o timer.
 */
(function () {
    'use strict';

    var IDLE_MINUTOS  = 30;          // minutos sem atividade → logout
    var AVISO_SEGUNDOS = 60;         // segundos de aviso antes do logout
    var LOGOUT_URL    = '/logout';
    var CSRF_TOKEN    = (document.querySelector('meta[name=csrf-token]') || {}).content || '';

    var timerIdle, timerContagem;
    var modalAberto = false;

    // ── Cria o modal de aviso ──────────────────────────────────────────
    function criarModal() {
        var div = document.createElement('div');
        div.id = 'idle-modal';
        div.style.cssText = [
            'display:none;position:fixed;inset:0;z-index:99999',
            'background:rgba(0,0,0,.6);align-items:center;justify-content:center',
        ].join(';');
        div.innerHTML = [
            '<div style="background:#fff;border-radius:12px;padding:32px;max-width:380px;width:90%;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.3)">',
            '  <div style="font-size:2.5rem;margin-bottom:12px">⏰</div>',
            '  <h5 style="font-weight:700;color:#1a1a2e;margin-bottom:8px">Sessão prestes a expirar</h5>',
            '  <p style="color:#666;font-size:.9rem;margin-bottom:20px">',
            '    Você está inativo. O sistema será desconectado em<br>',
            '    <strong id="idle-contador" style="font-size:1.6rem;color:#c0392b">60</strong> segundos.',
            '  </p>',
            '  <button id="idle-continuar" style="',
            '    background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border:none;',
            '    border-radius:8px;padding:10px 28px;font-size:.95rem;font-weight:700;cursor:pointer">',
            '    Continuar conectado',
            '  </button>',
            '</div>',
        ].join('');
        document.body.appendChild(div);

        document.getElementById('idle-continuar').addEventListener('click', fecharModal);
        return div;
    }

    var modal = null;

    function abrirModal() {
        if (modalAberto) return;
        modalAberto = true;
        if (!modal) modal = criarModal();
        modal.style.display = 'flex';

        var segundos = AVISO_SEGUNDOS;
        document.getElementById('idle-contador').textContent = segundos;

        timerContagem = setInterval(function () {
            segundos--;
            var el = document.getElementById('idle-contador');
            if (el) el.textContent = segundos;
            if (segundos <= 0) {
                clearInterval(timerContagem);
                fazerLogout();
            }
        }, 1000);
    }

    function fecharModal() {
        modalAberto = false;
        if (modal) modal.style.display = 'none';
        clearInterval(timerContagem);
        reiniciarTimer();
    }

    function fazerLogout() {
        // POST no endpoint de logout (protegido por CSRF)
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = LOGOUT_URL;
        var csrf = document.createElement('input');
        csrf.type  = 'hidden';
        csrf.name  = '_token';
        csrf.value = CSRF_TOKEN;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    function reiniciarTimer() {
        clearTimeout(timerIdle);
        timerIdle = setTimeout(abrirModal, IDLE_MINUTOS * 60 * 1000);
    }

    // ── Detecta atividade do usuário ──────────────────────────────────
    var eventos = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'];
    eventos.forEach(function (ev) {
        document.addEventListener(ev, function () {
            if (!modalAberto) reiniciarTimer();
        }, { passive: true });
    });

    // Inicia o timer quando a página carrega
    document.addEventListener('DOMContentLoaded', reiniciarTimer);

})();
