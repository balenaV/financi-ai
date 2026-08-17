<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function store(CategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create([
            ...$request->validated(),
            'active' => $request->boolean('active', true),
        ]);

        return redirect(route('dashboard').'#categorias')->with('success', 'Categoria criada com sucesso.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update([
            ...$request->validated(),
            'active' => $request->boolean('active'),
        ]);

        return redirect(route('dashboard').'#categorias')->with('success', 'Categoria atualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->transactions()->exists() || $category->children()->exists()) {
            return redirect(route('dashboard').'#categorias')->with('error', 'A categoria está em uso. Desative-a ou reorganize os registros primeiro.');
        }

        $category->delete();

        return redirect(route('dashboard').'#categorias')->with('success', 'Categoria excluída.');
    }
}
