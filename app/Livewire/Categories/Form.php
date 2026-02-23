<?php

namespace App\Livewire\Categories;

use App\Domain\Categories\Actions\UpsertCategoryAction;
use App\Domain\Categories\DTO\UpsertCategoryData;
use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $categoryId = null;

    public string $categoryTypeId = '';

    public string $name = '';

    public function mount(?Category $category = null): void
    {
        if ($category?->exists && $category->user_id !== Auth::id()) {
            abort(404);
        }

        if ($category?->exists) {
            $this->categoryId = $category->id;
            $this->categoryTypeId = (string) $category->category_type_id;
            $this->name = $category->name;

            return;
        }

        $this->categoryTypeId = (string) CategoryType::query()
            ->where('key', 'expense')
            ->value('id');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'categoryTypeId' => ['required', 'exists:category_types,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $category = app(UpsertCategoryAction::class)->execute(
            UpsertCategoryData::fromArray([
                'user_id' => Auth::id(),
                'category_id' => $this->categoryId,
                'category_type_id' => (int) $validated['categoryTypeId'],
                'name' => $validated['name'],
            ])
        );

        session()->flash('status', $category->wasRecentlyCreated
            ? 'Kategorija je uspešno dodata.'
            : 'Kategorija je uspešno izmenjena.');

        $this->redirectRoute('categories.index');
    }

    public function render(): View
    {
        return view('livewire.categories.form', [
            'isEditing' => $this->categoryId !== null,
            'types' => CategoryType::query()->orderBy('id')->get(),
        ])->layout('layouts.app', [
            'title' => $this->categoryId ? 'Izmena kategorije' : 'Nova kategorija',
        ]);
    }
}
