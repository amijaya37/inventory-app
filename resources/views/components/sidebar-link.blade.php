@props([
    'href',
    'label',
    'active' => null
])

@php
$isActive = $active ?? (request()->url() === $href || str_starts_with(request()->url(), $href . '?') || (request()->route() && str_starts_with(request()->route()->getName(), explode('.', $href)[0] ?? '')));
@endphp

<a href="{{ $href }}"
   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 {{ $isActive ? 'bg-indigo-600/20 text-indigo-300 border-l-2 border-indigo-500 pl-2.5' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
    {{ $label }}
</a>

