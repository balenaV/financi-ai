@props(['name', 'title'])
<div id="{{ $name }}" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title">
    <button type="button" class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" data-modal-close aria-label="Fechar"></button>
    <div class="surface relative mx-auto mt-[8vh] w-[calc(100%-2rem)] max-w-lg bg-surface-elevated p-6 shadow-xl">
        <div class="flex items-center justify-between gap-4">
            <h2 id="{{ $name }}-title" class="text-lg font-semibold text-foreground">{{ $title }}</h2>
            <button type="button" data-modal-close class="btn !size-10 !min-h-10 !p-0" aria-label="Fechar">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="mt-5">{{ $slot }}</div>
    </div>
</div>
