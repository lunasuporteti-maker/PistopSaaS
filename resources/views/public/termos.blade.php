<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Termos de Uso — PitStop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin:0; padding:0; background:#f9fafb; font-family:'Geist',Arial,sans-serif; color:#1a1a1a; }
        .container { max-width:720px; margin:0 auto; padding:40px 24px 80px; }
        header { background:#111827; color:#fff; padding:16px 24px; text-align:center; }
        header span { font-size:20px; font-weight:700; }
        h1 { font-size:24px; margin:32px 0 8px; color:#111827; }
        h2 { font-size:16px; margin:28px 0 8px; color:#1f2937; font-weight:700; }
        p, li { font-size:14px; line-height:1.75; color:#374151; }
        ul { padding-left:20px; }
        .meta { font-size:12px; color:#9ca3af; margin-bottom:32px; }
        hr { border:none; border-top:1px solid #e5e7eb; margin:24px 0; }
        footer { text-align:center; padding:24px; font-size:12px; color:#9ca3af; }
        a { color:#2563eb; }
    </style>
</head>
<body>

<header>
    <span>pit<span style="color:#f97316">stop</span></span>
</header>

<div class="container">

    <h1>Termos de Uso</h1>
    <p class="meta">Versão 1.0 — Última atualização: junho de 2025</p>

    <p>
        Bem-vindo ao <strong>PitStop</strong>, plataforma de gestão para oficinas mecânicas,
        desenvolvida e operada pela <strong>IAQueAtende</strong> ("nós" ou "PitStop").
        Ao criar uma conta e usar o PitStop, você ("Usuário") concorda integralmente com estes
        Termos de Uso. Leia com atenção antes de prosseguir.
    </p>

    <hr>

    <h2>1. O Serviço</h2>
    <p>
        O PitStop é um software como serviço (SaaS) que permite gerenciar clientes, veículos,
        orçamentos, ordens de serviço, financeiro e relatórios de oficinas mecânicas.
        O acesso é fornecido mediante assinatura mensal ou por meio de período de avaliação gratuito (trial).
    </p>

    <h2>2. Cadastro e Conta</h2>
    <ul>
        <li>Você deve ser maior de 18 anos ou ter representação legal para contratar o serviço.</li>
        <li>As informações fornecidas no cadastro devem ser verdadeiras e atualizadas.</li>
        <li>Você é responsável por manter a confidencialidade de sua senha e login.</li>
        <li>Cada conta está associada a um único tenant (oficina). Compartilhamento de credenciais entre
            diferentes oficinas não é permitido.</li>
    </ul>

    <h2>3. Planos e Pagamento</h2>
    <ul>
        <li>O trial gratuito tem duração de 30 dias e limites de uso definidos em nossa página de planos.</li>
        <li>Após o trial, é necessário assinar um plano para manter o acesso completo.</li>
        <li>O pagamento é processado mensalmente pela plataforma <strong>Asaas</strong>.</li>
        <li>Não há reembolso proporcional por cancelamento antecipado dentro do período pago.</li>
        <li>Os valores dos planos podem ser alterados com 30 dias de aviso prévio por e-mail.</li>
    </ul>

    <h2>4. Uso Aceitável</h2>
    <p>É proibido:</p>
    <ul>
        <li>Utilizar o PitStop para fins ilegais ou fraudulentos.</li>
        <li>Tentar acessar dados de outros tenants sem autorização.</li>
        <li>Realizar engenharia reversa ou tentativas de invasão da plataforma.</li>
        <li>Revender ou sublicenciar o acesso ao PitStop a terceiros.</li>
    </ul>

    <h2>5. Dados e Privacidade</h2>
    <p>
        O tratamento de dados pessoais é regido pela nossa
        <a href="{{ url('/privacidade') }}">Política de Privacidade</a>,
        em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei 13.709/2018).
        Seus dados são armazenados em servidores seguros e nunca são vendidos a terceiros.
    </p>

    <h2>6. Disponibilidade</h2>
    <p>
        Nos esforçamos para manter o PitStop disponível 24/7, mas não garantimos disponibilidade
        ininterrupta. Manutenções programadas serão comunicadas com antecedência.
    </p>

    <h2>7. Propriedade Intelectual</h2>
    <p>
        Todo o código, design, marca e conteúdo do PitStop são propriedade da IAQueAtende.
        Os dados que você inserir no sistema permanecem de sua propriedade.
    </p>

    <h2>8. Encerramento de Conta</h2>
    <p>
        Você pode cancelar sua assinatura a qualquer momento pelo painel de <strong>Assinatura</strong>.
        Mantemos seus dados por 90 dias após o cancelamento para possibilitar a reativação.
        Após esse prazo, os dados são excluídos permanentemente.
    </p>

    <h2>9. Limitação de Responsabilidade</h2>
    <p>
        O PitStop é fornecido "como está". Não nos responsabilizamos por perda de dados causada
        por falha do usuário, por danos indiretos ou lucros cessantes decorrentes do uso ou
        impossibilidade de uso do serviço.
    </p>

    <h2>10. Alterações nos Termos</h2>
    <p>
        Podemos atualizar estes Termos a qualquer momento. Você será notificado por e-mail com
        antecedência mínima de 15 dias. O uso contínuo após a vigência das alterações implica aceitação.
    </p>

    <h2>11. Contato e Foro</h2>
    <p>
        Para dúvidas sobre estes Termos, entre em contato pelo e-mail
        <a href="mailto:iaqueatende@gmail.com">iaqueatende@gmail.com</a>.
        Fica eleito o foro da Comarca de Recife — PE para dirimir quaisquer controvérsias.
    </p>

</div>

<footer>
    © {{ date('Y') }} PitStop · IAQueAtende ·
    <a href="{{ url('/privacidade') }}">Política de Privacidade</a>
</footer>

</body>
</html>
