<?php

namespace App\Livewire\Settings\IssueCategories;

use App\Domain\Settings\Issues\Actions\DeleteIssueCategoryAction;
use App\Domain\Settings\Issues\DTO\DeleteIssueCategoryData;
use App\Models\IssueCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deleteCategory(int $categoryId): void
    {
        app(DeleteIssueCategoryAction::class)->execute(
            DeleteIssueCategoryData::fromArray([
                'category_id' => $categoryId,
            ])
        );
        session()->flash('status', 'Kategorija je obrisana.');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-categories.index', [
            'categories' => IssueCategory::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => 'Issue kategorije',
        ]);
    }
}
