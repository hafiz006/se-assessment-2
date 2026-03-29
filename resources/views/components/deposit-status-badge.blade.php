@props(['status'])

@php
    $classes = match($status->value) {
        'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'confirmed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'withdrawn' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400',
        default     => 'bg-zinc-100 text-zinc-500',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold $classes"]) }}>
    {{ ucfirst($status->value) }}
</span>
