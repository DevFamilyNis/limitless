<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail($categoryId);

        $category->delete();

        session()->flash('status', 'Kategorija je uspešno obrisana.');
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('type')
            ->where('user_id', Auth::id())
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->typeFilter !== 'all', function ($query): void {
                $query->whereHas('type', fn ($typeQuery) => $typeQuery->where('key', $this->typeFilter));
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.categories.index', [
            'categories' => $categories,
            'types' => CategoryType::query()->orderBy('id')->get(),
        ])->layout('layouts.app', [
            'title' => 'Kategorije',
        ]);
    }
}
