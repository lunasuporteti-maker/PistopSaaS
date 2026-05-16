/**
 * PitStop — Padronização global de inputs
 * Uppercase automático, somente números, sem caracteres especiais
 */
(function () {
    'use strict';

    // Seletores que NÃO devem virar maiúsculo
    var SKIP_UPPER = 'input[type="password"], input[type="email"], input[type="url"], input[type="search"], input[type="hidden"], input[data-no-upper], textarea[data-no-upper]';

    function aplicarUppercaseGlobal() {
        // Aplica uppercase em todos os inputs de texto e textareas,
        // exceto os tipos que não devem ser maiúsculos
        var seletor = 'input[type="text"], input:not([type]), textarea';
        document.querySelectorAll(seletor).forEach(function (el) {
            if (el._globalUpperBound) return;
            if (el.matches(SKIP_UPPER)) return;
            el._globalUpperBound = true;
            el.style.textTransform = 'uppercase';
            el.addEventListener('input', function () {
                var pos = el.selectionStart;
                el.value = el.value.toUpperCase();
                try { el.setSelectionRange(pos, pos); } catch (e) {}
            });
            el.value = el.value.toUpperCase();
        });
    }

    function aplicarMascaras() {
        aplicarUppercaseGlobal();

        // ── Uppercase explícito (data-uppercase — mantido por compatibilidade) ──
        document.querySelectorAll('[data-uppercase]').forEach(function (el) {
            if (el._upperBound) return;
            el._upperBound = true;
            el.addEventListener('input', function () {
                var pos = el.selectionStart;
                el.value = el.value.toUpperCase();
                try { el.setSelectionRange(pos, pos); } catch (e) {}
            });
            el.value = el.value.toUpperCase();
        });

        // ── Somente números (dígitos) ─────────────────────────────────────
        document.querySelectorAll('[data-only-numbers]').forEach(function (el) {
            if (el._numBound) return;
            el._numBound = true;
            el.addEventListener('keypress', function (e) {
                if (!/[0-9]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) {
                    e.preventDefault();
                }
            });
            el.addEventListener('input', function () {
                el.value = el.value.replace(/[^0-9]/g, '');
            });
        });

        // ── Telefone: aceita dígitos, espaço, parênteses e hífen ─────────
        document.querySelectorAll('[data-phone]').forEach(function (el) {
            if (el._phoneBound) return;
            el._phoneBound = true;
            el.addEventListener('input', function () {
                // Remove tudo exceto dígitos
                var digits = el.value.replace(/\D/g, '').substring(0, 11);
                // Formata: (99) 99999-9999
                var f = digits;
                if (digits.length > 2)  f = '(' + digits.substring(0,2) + ') ' + digits.substring(2);
                if (digits.length > 7)  f = '(' + digits.substring(0,2) + ') ' + digits.substring(2,7) + '-' + digits.substring(7);
                el.value = f;
            });
        });

        // ── CPF: somente números com máscara 000.000.000-00 ──────────────
        document.querySelectorAll('[data-cpf]').forEach(function (el) {
            if (el._cpfBound) return;
            el._cpfBound = true;
            el.addEventListener('input', function () {
                var d = el.value.replace(/\D/g, '').substring(0, 11);
                var f = d;
                if (d.length > 3)  f = d.substring(0,3) + '.' + d.substring(3);
                if (d.length > 6)  f = d.substring(0,3) + '.' + d.substring(3,6) + '.' + d.substring(6);
                if (d.length > 9)  f = d.substring(0,3) + '.' + d.substring(3,6) + '.' + d.substring(6,9) + '-' + d.substring(9);
                el.value = f;
            });
        });

        // ── Placa: máscara ABC-1234 ou ABC1D23 ───────────────────────────
        document.querySelectorAll('[data-placa]').forEach(function (el) {
            if (el._placaBound) return;
            el._placaBound = true;
            el.addEventListener('input', function () {
                el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 7);
            });
        });

        // ── Sem caracteres especiais e sem números (nomes) ────────────────
        document.querySelectorAll('[data-no-special]').forEach(function (el) {
            if (el._noSpecialBound) return;
            el._noSpecialBound = true;
            el.addEventListener('input', function () {
                // Permite letras (incluindo acentuadas), espaços e hífens
                el.value = el.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
            });
        });

        // ── Apenas letras e espaços (sem números, sem especiais) ─────────
        // (alias para data-no-special mas mais explícito)
        document.querySelectorAll('[data-alpha-only]').forEach(function (el) {
            if (el._alphaBound) return;
            el._alphaBound = true;
            el.addEventListener('input', function () {
                el.value = el.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            });
        });

        // ── KM / Ano: somente números inteiros ───────────────────────────
        document.querySelectorAll('[data-km], [data-ano]').forEach(function (el) {
            if (el._kmBound) return;
            el._kmBound = true;
            el.setAttribute('inputmode', 'numeric');
            el.addEventListener('input', function () {
                el.value = el.value.replace(/\D/g, '');
            });
        });

        // ── Valor monetário: aceita números e vírgula/ponto ──────────────
        document.querySelectorAll('[data-money]').forEach(function (el) {
            if (el._moneyBound) return;
            el._moneyBound = true;
            el.addEventListener('keypress', function (e) {
                if (!/[0-9.,]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        });
    }

    // Aplica na carga da página
    document.addEventListener('DOMContentLoaded', aplicarMascaras);

    // Re-aplica quando modais são abertos (jQuery/Bootstrap)
    if (typeof $ !== 'undefined') {
        $(document).on('shown.bs.modal', aplicarMascaras);
        // Também para conteúdo dinâmico via AJAX
        $(document).ajaxComplete(function () { setTimeout(aplicarMascaras, 100); });
    }

    // Expõe a função globalmente para uso em conteúdo dinâmico
    window.pitStopAplicarMascaras = aplicarMascaras;
})();
