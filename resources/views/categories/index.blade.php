<x-app-layout>
    <x-slot name="title">Categorias</x-slot>
    <x-page-header title="Categorias" description="Organize receitas e despesas para entender seus hábitos." />
    <div class="grid gap-6 xl:grid-cols-[.75fr_1.25fr]">
        <form method="POST" action="{{ route('categories.store') }}" class="surface h-fit p-5">
            @csrf <h2 class="font-bold">Nova categoria</h2>
            <div class="mt-5 space-y-4">
                <x-form.input label="Nome" name="name" required />
                <x-form.select label="Tipo" name="type" required><option value="">Selecione</option>@foreach($types as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</x-form.select>
                <x-form.select label="Categoria pai" name="parent_id"><option value="">Nenhuma</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</x-form.select>
                <div class="grid grid-cols-2 gap-4"><x-form.input label="Cor" name="color" type="color" value="#64748b" required /><x-form.select label="Ícone" name="icon" required><option value="tag">Etiqueta</option><option value="home">Casa</option><option value="food">Alimentação</option><option value="car">Transporte</option><option value="heart">Saúde</option></x-form.select></div>
                <input type="hidden" name="active" value="1"><x-button type="submit" class="w-full">Criar categoria</x-button>
            </div>
        </form>
        <div class="surface overflow-hidden">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Categoria</th><th class="px-5 py-3">Tipo</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Ações</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($categories as $category)<tr><td class="px-5 py-4"><div class="flex items-center gap-3"><span class="size-3 rounded-full" style="background: {{ $category->color }}"></span><span><strong class="block">{{ $category->name }}</strong>@if($category->parent)<small class="text-slate-500">em {{ $category->parent->name }}</small>@endif</span></div></td><td class="px-5 py-4"><x-badge :tone="$category->type->value">{{ $category->type->label() }}</x-badge></td><td class="px-5 py-4"><x-badge :tone="$category->active ? 'success' : 'neutral'">{{ $category->active ? 'Ativa' : 'Inativa' }}</x-badge></td><td class="px-5 py-4"><div class="flex justify-end gap-1"><a href="{{ route('categories.edit', $category) }}" class="btn !min-h-9 !px-3 text-primary-600 hover:bg-primary-50">Editar</a><form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="Excluir esta categoria?">@csrf @method('DELETE')<button class="btn !min-h-9 !px-3 text-red-600 hover:bg-red-50">Excluir</button></form></div></td></tr>
            @empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Nenhuma categoria cadastrada.</td></tr>@endforelse
            </tbody></table></div><div class="px-5 py-4">{{ $categories->links() }}</div>
        </div>
    </div>
</x-app-layout>
