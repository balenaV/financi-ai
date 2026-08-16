<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('categories.index', [
            'categories' => $request->user()->categories()->with('parent')->orderBy('type')->orderBy('name')->paginate(30),
            'parents' => $request->user()->categories()->whereNull('parent_id')->orderBy('name')->get(),
            'types' => CategoryType::cases(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create([
            ...$request->validated(),
            'active' => $request->boolean('active', true),
        ]);

        return back()->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('categories.form', [
            'category' => $category,
            'parents' => $category->user->categories()->whereKeyNot($category->id)->orderBy('name')->get(),
            'types' => CategoryType::cases(),
        ]);
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
            return back()->with('error', 'A categoria está em uso. Desative-a ou reorganize os registros primeiro.');
        }

        $category->delete();

        return back()->with('success', 'Categoria excluída.');
    }
}
