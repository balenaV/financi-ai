@props(['title', 'message', 'action' => null])
<div class="surface flex flex-col items-center px-6 py-14 text-center">
    <span class="grid size-12 place-items-center rounded-2xl bg-primary-50 text-xl font-bold text-primary-600">+</span>
    <h3 class="mt-4 font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 max-w-md text-sm text-slate-500">{{ $message }}</p>
    @if($action)<div class="mt-5">{{ $action }}</div>@endif
</div>
