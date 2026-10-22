@props(['status'])
@php
    $kelas = match ($status) {
        \App\Enums\StatusSurat::Publish => ['bg-emerald-50 text-emerald-800 ring-emerald-600/20', 'bg-emerald-600'],
        \App\Enums\StatusSurat::Draf => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        default => ['bg-red-50 text-red-800 ring-red-600/20', 'bg-red-600'],
    };
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $kelas[0]]) }}>
    <span @class(['size-1.5 rounded-full', $kelas[1]])></span>
    {{ $status->label() }}
</span>
