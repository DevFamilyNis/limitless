<?php

namespace Database\Seeders;

use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use Illuminate\Database\Seeder;

class IssueDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        IssueStatus::query()->upsert([
            ['key' => 'backlog', 'name' => 'Backlog', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'todo', 'name' => 'To Do', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'doing', 'name' => 'Doing', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'done', 'name' => 'Done', 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['name', 'sort_order', 'is_active', 'updated_at']);

        IssuePriority::query()->upsert([
            ['key' => 'low', 'name' => 'Low', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'medium', 'name' => 'Medium', 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'high', 'name' => 'High', 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'urgent', 'name' => 'Urgent', 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['name', 'sort_order', 'updated_at']);

        foreach (['Bug', 'Feature', 'Support', 'Task', 'Reminder'] as $name) {
            IssueCategory::query()->firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
