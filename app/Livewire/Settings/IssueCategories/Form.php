<?php

namespace App\Livewire\Settings\IssueCategories;

use App\Domain\Settings\Issues\Actions\UpsertIssueCategoryAction;
use App\Domain\Settings\Issues\DTO\UpsertIssueCategoryData;
use App\Models\IssueCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?int $categoryId = null;

    public string $name = '';

    public bool $isActive = true;

    public function mount(?IssueCategory $issueCategory = null): void
    {
        if ($issueCategory?->exists) {
            $this->categoryId = $issueCategory->id;
            $this->name = $issueCategory->name;
            $this->isActive = $issueCategory->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        app(UpsertIssueCategoryAction::class)->execute(
            UpsertIssueCategoryData::fromArray([
                'category_id' => $this->categoryId,
                'name' => $validated['name'],
                'is_active' => (bool) $validated['isActive'],
            ])
        );

        $this->redirectRoute('settings.issue-categories.index');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-categories.form', [
            'isEditing' => $this->categoryId !== null,
        ])->layout('layouts.app', [
            'title' => $this->categoryId ? __('messages.settings.issue_categories.edit_title') : __('messages.settings.issue_categories.new_title'),
        ]);
    }
}
