@props(['name', 'title'])
<div id="{{ $name }}" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title">
    <button type="button" class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" data-modal-close aria-label="Fechar"></button>
    <div class="relative mx-auto mt-[8vh] w-[calc(100%-2rem)] max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <h2 id="{{ $name }}-title" class="text-lg font-bold text-slate-900">{{ $title }}</h2>
            <button type="button" data-modal-close class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100" aria-label="Fechar">×</button>
        </div>
        <div class="mt-5">{{ $slot }}</div>
    </div>
</div>
