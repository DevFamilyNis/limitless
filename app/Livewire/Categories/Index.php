<?php

namespace App\Livewire\Categories;

use App\Domain\Categories\Actions\DeleteCategoryAction;
use App\Domain\Categories\DTO\DeleteCategoryData;
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
        app(DeleteCategoryAction::class)->execute(
            DeleteCategoryData::fromArray([
                'user_id' => Auth::id(),
                'category_id' => $categoryId,
            ])
        );

        session()->flash('status', __('messages.categories.flash_deleted'));
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('type')
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
            'title' => __('messages.categories.title'),
        ]);
    }
}
