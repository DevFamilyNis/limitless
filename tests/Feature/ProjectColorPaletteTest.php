<?php

use App\Models\Project;
use App\Support\ProjectColorPalette;

test('project color palette is deterministic for the same project', function () {
    $project = new Project([
        'id' => 42,
        'code' => 'DIN-OPS',
        'name' => 'DIN Operations',
    ]);

    $first = ProjectColorPalette::for($project);
    $second = ProjectColorPalette::for($project);

    expect($first)->toBe($second);
    expect($first)->toHaveKeys(['name', 'hex', 'rgb', 'soft_bg', 'strong_bg', 'border']);
});

test('empay projects always use blue palette', function () {
    $project = new Project([
        'id' => 7,
        'code' => 'EMPAY-STARI',
        'name' => 'Empay stari',
    ]);

    $color = ProjectColorPalette::for($project);

    expect($color['name'])->toBe('blue');
    expect($color['hex'])->toBe('#2563eb');
});

test('novi empay projects use zinc palette', function () {
    $project = new Project([
        'id' => 8,
        'code' => 'EMPAY-NOVI',
        'name' => 'Novi Empay',
    ]);

    $color = ProjectColorPalette::for($project);

    expect($color['name'])->toBe('zinc');
    expect($color['hex'])->toBe('#52525b');
});

test('empay2 projects use zinc palette', function () {
    $project = new Project([
        'id' => 11,
        'code' => 'EMPAY2.0',
        'name' => 'Empay2.0',
    ]);

    $color = ProjectColorPalette::for($project);

    expect($color['name'])->toBe('zinc');
    expect($color['hex'])->toBe('#52525b');
});

test('fm projects use teal palette', function () {
    $project = new Project([
        'id' => 9,
        'code' => 'FM-OPS',
        'name' => 'Facility FM',
    ]);

    $color = ProjectColorPalette::for($project);

    expect($color['name'])->toBe('teal');
    expect($color['hex'])->toBe('#0d9488');
});

test('manually selected project color overrides fallback rules', function () {
    $project = new Project([
        'id' => 10,
        'code' => 'EMPAY-STARI',
        'name' => 'Empay stari',
        'project_color' => 'emerald',
    ]);

    $color = ProjectColorPalette::for($project);

    expect($color['name'])->toBe('emerald');
    expect($color['hex'])->toBe('#059669');
});
