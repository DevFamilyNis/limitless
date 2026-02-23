<?php

namespace App\Livewire\Settings\IssueCategories;

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

        $category = $this->categoryId ? IssueCategory::query()->findOrFail($this->categoryId) : new IssueCategory;

        $category->fill([
            'name' => trim($validated['name']),
            'is_active' => (bool) $validated['isActive'],
        ]);
        $category->save();

        $this->redirectRoute('settings.issue-categories.index');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-categories.form', [
            'isEditing' => $this->categoryId !== null,
        ])->layout('layouts.app', [
            'title' => $this->categoryId ? 'Izmena kategorije' : 'Nova kategorija',
        ]);
    }
}
