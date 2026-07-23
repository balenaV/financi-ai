@props(['title', 'description' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ $title }}</h1>
        @if($description)<p class="mt-1 text-sm text-slate-500">{{ $description }}</p>@endif
    </div>
    @if(trim((string) $slot))<div class="flex flex-wrap gap-2">{{ $slot }}</div>@endif
</div>
