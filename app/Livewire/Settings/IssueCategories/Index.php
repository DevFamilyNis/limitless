<?php

namespace App\Livewire\Settings\IssueCategories;

use App\Models\IssueCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deleteCategory(int $categoryId): void
    {
        IssueCategory::query()->findOrFail($categoryId)->delete();
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
