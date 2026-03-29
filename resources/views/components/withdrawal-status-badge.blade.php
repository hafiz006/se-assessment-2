@props(['status'])

@php
    $classes = match($status->value) {
        'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'approved'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'rejected'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        default     => 'bg-zinc-100 text-zinc-500',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold $classes"]) }}>
    {{ ucfirst($status->value) }}
</span>
