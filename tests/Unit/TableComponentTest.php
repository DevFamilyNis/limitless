<?php

test('ui table component includes horizontal scroll wrapper', function () {
    $template = file_get_contents(__DIR__.'/../../resources/views/components/ui/table/index.blade.php');

    expect($template)->not->toBeFalse();
    expect($template)->toContain('overflow-x-auto');
    expect($template)->toContain('overflow-hidden');
});
