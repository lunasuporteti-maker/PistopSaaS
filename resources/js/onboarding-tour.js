/**
 * Story 8.2 — Tour de onboarding guiado (5 passos, zero deps).
 */
(function () {
  'use strict';

  var steps = [
    {
      selector: '#mainSidebar',
      title: 'Menu de navegação',
      text: 'Use o menu lateral para acessar clientes, ordens de serviço, financeiro e configurações da sua oficina.',
    },
    {
      selector: '[data-tour="clientes"]',
      title: 'Comece pelos clientes',
      text: 'Cadastre seus clientes aqui. Cada cliente pode ter um ou mais veículos vinculados.',
    },
    {
      selector: '[data-tour="ordens"]',
      title: 'Ordens de Serviço',
      text: 'Crie e acompanhe ordens de serviço. Você pode criar uma OS a partir de um orçamento aprovado.',
    },
    {
      selector: '[data-tour="financeiro"]',
      title: 'Controle financeiro',
      text: 'Registre entradas, saídas e acompanhe o fluxo de caixa da oficina nesta seção.',
    },
    {
      selector: '[data-tour="perfil"]',
      title: 'Seu perfil',
      text: 'Configure seu perfil, altere a senha e gerencie os usuários da oficina aqui.',
    },
  ];

  var current = 0;
  var overlay, tooltip, highlighted;
  var concluirUrl = document.getElementById('tour-root').dataset.concluirUrl;
  var csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

  function init() {
    overlay = document.createElement('div');
    overlay.id = 'tour-overlay';
    document.body.appendChild(overlay);

    tooltip = document.createElement('div');
    tooltip.id = 'tour-tooltip';
    document.body.appendChild(tooltip);

    showStep(0);
  }

  function showStep(index) {
    current = index;
    var step = steps[index];
    var el   = document.querySelector(step.selector);

    // Remove highlight anterior
    if (highlighted) highlighted.classList.remove('tour-highlight');

    if (el) {
      el.classList.add('tour-highlight');
      highlighted = el;
      positionTooltip(el);
    }

    var isLast = index === steps.length - 1;

    tooltip.innerHTML =
      '<div class="tour-tooltip-title">' + step.title + '</div>' +
      '<div class="tour-tooltip-text">' + step.text + '</div>' +
      '<div class="tour-tooltip-footer">' +
        '<span class="tour-step-indicator">' + (index + 1) + ' / ' + steps.length + '</span>' +
        '<div class="tour-actions">' +
          '<button class="btn-tour-skip" id="btn-tour-skip">Pular tour</button>' +
          '<button class="btn-tour-next" id="btn-tour-next">' + (isLast ? 'Concluir ✓' : 'Próximo →') + '</button>' +
        '</div>' +
      '</div>';

    document.getElementById('btn-tour-skip').onclick = finish;
    document.getElementById('btn-tour-next').onclick = isLast ? finish : function () { showStep(current + 1); };
  }

  function positionTooltip(el) {
    var rect   = el.getBoundingClientRect();
    var ttW    = 280;
    var margin = 12;
    var isMobile = window.innerWidth < 768;

    tooltip.className = '';

    var top, left;

    if (isMobile) {
      // Mobile: posiciona abaixo do elemento
      top  = rect.bottom + margin;
      left = Math.max(8, Math.min(rect.left, window.innerWidth - ttW - 8));
      tooltip.classList.add('arrow-left');
    } else if (rect.right + ttW + margin < window.innerWidth) {
      // Direita do elemento
      top  = rect.top;
      left = rect.right + margin;
      tooltip.classList.add('arrow-right');
    } else {
      // Abaixo do elemento
      top  = rect.bottom + margin;
      left = Math.max(8, rect.left);
      tooltip.classList.add('arrow-left');
    }

    // Garante que não saia da viewport
    top  = Math.max(8, Math.min(top, window.innerHeight - 200));
    left = Math.max(8, Math.min(left, window.innerWidth - ttW - 8));

    tooltip.style.top  = top + 'px';
    tooltip.style.left = left + 'px';
  }

  function finish() {
    // Remove elementos do DOM
    if (highlighted) highlighted.classList.remove('tour-highlight');
    overlay.remove();
    tooltip.remove();

    // Salva via AJAX
    fetch(concluirUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    }).catch(function () {
      // Silencia falha de rede — tour não deve travar o usuário
    });
  }

  // Aguarda DOM pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
