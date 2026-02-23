<?php

use Illuminate\Support\Facades\Schema;

test('issues tables have expected structure', function () {
    expect(Schema::hasTable('issue_statuses'))->toBeTrue();
    expect(Schema::hasTable('issue_priorities'))->toBeTrue();
    expect(Schema::hasTable('issue_categories'))->toBeTrue();
    expect(Schema::hasTable('issues'))->toBeTrue();
    expect(Schema::hasTable('issue_comments'))->toBeTrue();

    expect(Schema::hasColumns('issues', [
        'project_id',
        'client_id',
        'client_contact_id',
        'status_id',
        'priority_id',
        'category_id',
        'author_id',
        'assignee_id',
        'title',
        'description',
        'due_date',
        'completed_at',
    ]))->toBeTrue();
});
