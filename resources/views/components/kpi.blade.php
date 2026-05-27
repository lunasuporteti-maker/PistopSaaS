{{-- x-kpi: widget de KPI com label, valor e delta opcional
     Uso:
       <x-kpi label="Receita do mês" value="R$ 38.900,00" delta="+8,1%" :deltaPos="true" />
       <x-kpi label="OS Abertas" value="12" />  --}}
@props(['label', 'value', 'delta' => null, 'deltaPos' => true])

<div class="kpi">
    <div class="kpi-label">{{ $label }}</div>
    <div class="kpi-value">{{ $value }}</div>
    @if ($delta)
        <div class="kpi-foot row-flex" style="gap:4px;font-size:11px">
            <span class="{{ $deltaPos ? 'delta-up' : 'delta-down' }}">{{ $delta }}</span>
            <span class="muted-2">vs. mês anterior</span>
        </div>
    @endif
</div>
