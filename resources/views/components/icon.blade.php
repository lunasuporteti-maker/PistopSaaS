{{-- x-icon: ícone SVG inline (Lucide-inspired, extraído do handoff)
     Uso: <x-icon name="wrench" size="16" />
     Uso: <x-icon name="plus" size="20" class="text-brand-500" />
     Nomes disponíveis: dashboard · kanban · wrench · receipt · users · car · box
       cash · chart · calendar · bell · search · settings · plus · check · x
       chevronRight · chevronLeft · chevronDown · arrowUp · arrowDown · arrowRight · arrowLeft
       menu · sun · moon · more · filter · download · print · edit · trash · whatsapp
       flag · clock · drag · user · phone · mail · link · printer · speed · layers
       shield · alert · zap · inbox · refresh · expand · history · star --}}
@props(['name', 'size' => 16, 'stroke' => '1.75'])

@php
// Mapeamento de nomes para SVG interno (path/conteúdo)
$icons = [
    'dashboard'    => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'kanban'       => '<rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="10" y="4" width="5" height="11" rx="1.5"/><rect x="17" y="4" width="4" height="8" rx="1.5"/>',
    'wrench'       => '<path d="M14.7 6.3a3.5 3.5 0 1 1 3 3l-8.3 8.3a2 2 0 0 1-2.8 0l-.2-.2a2 2 0 0 1 0-2.8z"/>',
    'receipt'      => '<path d="M5 3v18l2-1.5L9 21l2-1.5L13 21l2-1.5L17 21l2-1.5V3z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    'users'        => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M21 19c0-2-1.7-4-4-4"/>',
    'car'          => '<path d="M3 14l1.5-5a3 3 0 0 1 2.9-2.2h9.2A3 3 0 0 1 19.5 9L21 14"/><path d="M3 14h18v4a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><circle cx="7" cy="17" r="1"/><circle cx="17" cy="17" r="1"/>',
    'box'          => '<path d="M3 7l9-4 9 4-9 4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/>',
    'cash'         => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 10v4M18 10v4"/>',
    'chart'        => '<path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/>',
    'calendar'     => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/>',
    'bell'         => '<path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 19a2 2 0 0 0 4 0"/>',
    'search'       => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4-4"/>',
    'settings'     => '<circle cx="12" cy="12" r="2.8"/><path d="M19.4 14.5a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    'plus'         => '<path d="M12 5v14M5 12h14"/>',
    'check'        => '<path d="m5 12 5 5L20 7"/>',
    'x'            => '<path d="M18 6 6 18M6 6l12 12"/>',
    'chevronRight' => '<path d="m9 6 6 6-6 6"/>',
    'chevronLeft'  => '<path d="m15 6-6 6 6 6"/>',
    'chevronDown'  => '<path d="m6 9 6 6 6-6"/>',
    'arrowUp'      => '<path d="M12 19V5M5 12l7-7 7 7"/>',
    'arrowDown'    => '<path d="M12 5v14M5 12l7 7 7-7"/>',
    'arrowRight'   => '<path d="M5 12h14M12 5l7 7-7 7"/>',
    'arrowLeft'    => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
    'menu'         => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    'sun'          => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
    'moon'         => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
    'more'         => '<circle cx="6" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="18" cy="12" r="1"/>',
    'filter'       => '<path d="M3 5h18l-7 9v6l-4-2v-4z"/>',
    'download'     => '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>',
    'print'        => '<path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/>',
    'edit'         => '<path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>',
    'trash'        => '<path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>',
    'whatsapp'     => '<path d="M3 21l1.7-5.2A8.5 8.5 0 1 1 8.4 19.3z"/><path d="M9 9.5c0-1 .5-1.7 1.4-1.7.4 0 .6.2.9.8l.6 1.3c.2.4.2.7-.1 1l-.6.6c.6 1.2 1.4 2 2.6 2.6l.6-.6c.3-.3.6-.3 1-.1l1.3.6c.6.3.8.5.8.9 0 .9-.7 1.4-1.7 1.4-2.7 0-6.8-4.1-6.8-6.8z" fill="currentColor" stroke="none"/>',
    'flag'         => '<path d="M4 21V3M4 4h14l-2 4 2 4H4"/>',
    'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'drag'         => '<circle cx="9" cy="6" r=".8"/><circle cx="9" cy="12" r=".8"/><circle cx="9" cy="18" r=".8"/><circle cx="15" cy="6" r=".8"/><circle cx="15" cy="12" r=".8"/><circle cx="15" cy="18" r=".8"/>',
    'user'         => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
    'phone'        => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/>',
    'mail'         => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'link'         => '<path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
    'printer'      => '<path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/>',
    'speed'        => '<path d="M12 21a9 9 0 1 0-9-9"/><path d="M12 12l5-3"/>',
    'layers'       => '<path d="m12 2 10 6-10 6L2 8z"/><path d="m2 16 10 6 10-6"/><path d="m2 12 10 6 10-6"/>',
    'shield'       => '<path d="M12 2 4 5v6c0 5 3.5 9.5 8 11 4.5-1.5 8-6 8-11V5z"/>',
    'alert'        => '<path d="M10.3 3.3 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3l-8.5-14.7a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    'zap'          => '<path d="M13 2 3 14h7l-1 8 10-12h-7z"/>',
    'inbox'        => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
    'refresh'      => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M8 16H3v5"/>',
    'expand'       => '<path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>',
    'history'      => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/>',
    'star'         => '<path d="m12 2 3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>',
    'fornecedor'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'estoque'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
];

$svg = $icons[$name] ?? '<circle cx="12" cy="12" r="2"/>';
@endphp

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="{{ $stroke }}"
     stroke-linecap="round" stroke-linejoin="round"
     {{ $attributes->except(['name', 'size', 'stroke']) }}
     aria-hidden="true">{!! $svg !!}</svg>
