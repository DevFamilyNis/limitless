<?php

use App\Support\IssueLabelPalette;

test('issue status palette matches requested workflow colors', function () {
    expect(IssueLabelPalette::forStatus('backlog', 'Backlog')['name'])->toBe('slate');
    expect(IssueLabelPalette::forStatus('todo', 'To Do')['name'])->toBe('slate');
    expect(IssueLabelPalette::forStatus('doing', 'Doing')['name'])->toBe('slate');
    expect(IssueLabelPalette::forStatus('done', 'Done')['name'])->toBe('slate');
});

test('issue priority palette escalates from blue to red', function () {
    expect(IssueLabelPalette::forPriority('low', 'Low')['name'])->toBe('blue');
    expect(IssueLabelPalette::forPriority('medium', 'Medium')['name'])->toBe('amber');
    expect(IssueLabelPalette::forPriority('high', 'High')['name'])->toBe('orange');
    expect(IssueLabelPalette::forPriority('urgent', 'Urgent')['name'])->toBe('red');
});

test('medium priority has stronger contrast style', function () {
    $medium = IssueLabelPalette::forPriority('medium', 'Medium');

    expect($medium['font_weight'])->toBe(500);
    expect($medium['border_width'])->toBe('1px');
});

test('issue category palette follows common naming conventions', function () {
    expect(IssueLabelPalette::forCategory('Bug')['name'])->toBe('red');
    expect(IssueLabelPalette::forCategory('Feature')['name'])->toBe('violet');
    expect(IssueLabelPalette::forCategory('Support')['name'])->toBe('teal');
    expect(IssueLabelPalette::forCategory('Research')['name'])->toBe('cyan');
});

test('issue label palette can be centrally changed via config', function () {
    config()->set('issue-labels.status.default', 'red');

    expect(IssueLabelPalette::forStatus('unknown-status', 'Unknown status')['name'])->toBe('red');
});
