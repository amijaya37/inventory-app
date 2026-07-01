<?php

namespace App\Http\Controllers\Web\Master;

use App\Domain\Master\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreCategoryRequest;
use App\Http\Requests\Master\UpdateCategoryRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $query->where('is_active', $request->boolean('status'));
            })
            ->withCount('items');

        $categories = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.categories.index', [
            'categories' => $categories,
            'search' => $request->string('search')->toString(),
            'status' => $request->input('status'),
        ]);
    }

    public function create(): View
    {
        return view('master-data.categories.create', [
            'category' => new Category(['is_active' => true]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $userId = $request->user()?->id;

        Category::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('master-data.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->can('categories.delete'), 403);

        if ($category->items()->exists()) {
            $category->update([
                'is_active' => false,
                'updated_by' => $request->user()->id,
            ]);

            return redirect()
                ->route('categories.index')
                ->with('warning', 'Kategori sudah dipakai oleh barang, sehingga hanya dinonaktifkan.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
