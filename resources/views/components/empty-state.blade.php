@props(['title', 'message', 'action' => null])
<div {{ $attributes->merge(['class' => 'surface flex flex-col items-center gap-4 border-dashed px-6 py-10 text-center sm:flex-row sm:text-left']) }}>
    <img src="{{ asset('images/mascot/capi-sentado.png') }}" alt="" width="96" height="74" class="w-24 shrink-0" aria-hidden="true">
    <div>
        <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
        <p class="mt-1 max-w-md text-sm text-slate-500">{{ $message }}</p>
        @if($action)<div class="mt-4 flex justify-center sm:justify-start">{{ $action }}</div>@endif
    </div>
</div>
