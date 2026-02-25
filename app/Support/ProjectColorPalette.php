<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Project;

final class ProjectColorPalette
{
    /**
     * @var array<int, array{name:string,hex:string,rgb:string}>
     */
    private const PALETTE = [
        ['name' => 'blue', 'hex' => '#2563eb', 'rgb' => '37, 99, 235'],
        ['name' => 'slate', 'hex' => '#334155', 'rgb' => '51, 65, 85'],
        ['name' => 'zinc', 'hex' => '#52525b', 'rgb' => '82, 82, 91'],
        ['name' => 'teal', 'hex' => '#0d9488', 'rgb' => '13, 148, 136'],
        ['name' => 'emerald', 'hex' => '#059669', 'rgb' => '5, 150, 105'],
        ['name' => 'amber', 'hex' => '#d97706', 'rgb' => '217, 119, 6'],
        ['name' => 'rose', 'hex' => '#e11d48', 'rgb' => '225, 29, 72'],
        ['name' => 'violet', 'hex' => '#7c3aed', 'rgb' => '124, 58, 237'],
        ['name' => 'cyan', 'hex' => '#0891b2', 'rgb' => '8, 145, 178'],
        ['name' => 'fuchsia', 'hex' => '#c026d3', 'rgb' => '192, 38, 211'],
    ];

    /**
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,strong_bg:string,border:string}
     */
    public static function for(Project $project): array
    {
        if (is_string($project->project_color) && self::byName($project->project_color) !== null) {
            return self::buildColor(self::byName($project->project_color));
        }

        $seed = mb_strtolower(trim(sprintf('%s %s', (string) $project->code, (string) $project->name)));

        return self::buildColor(self::resolveBaseColor($seed, (string) $project->id));
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::PALETTE as $color) {
            $options[$color['name']] = ucfirst($color['name']);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        $dotMap = [
            'blue' => '🔵',
            'slate' => '🔘',
            'zinc' => '⚫',
            'teal' => '🟢',
            'emerald' => '🟢',
            'amber' => '🟠',
            'rose' => '🔴',
            'violet' => '🟣',
            'cyan' => '🔵',
            'fuchsia' => '🟣',
        ];

        $options = [];

        foreach (self::options() as $name => $label) {
            $options[$name] = sprintf('%s %s', $dotMap[$name] ?? '⚪', $label);
        }

        return $options;
    }

    public static function suggestedName(string $code, string $name, ?int $projectId = null): string
    {
        $seed = mb_strtolower(trim(sprintf('%s %s', $code, $name)));
        $fallback = (string) ($projectId ?? 0);
        $color = self::resolveBaseColor($seed, $fallback);

        return $color['name'];
    }

    /**
     * @return array{name:string,hex:string,rgb:string}|null
     */
    private static function byName(string $name): ?array
    {
        foreach (self::PALETTE as $color) {
            if ($color['name'] === $name) {
                return $color;
            }
        }

        return null;
    }

    /**
     * @return array{name:string,hex:string,rgb:string}
     */
    private static function resolveBaseColor(string $seed, string $fallback): array
    {
        if (
            $seed !== ''
            && (
                str_contains($seed, 'novi empay')
                || str_contains($seed, 'novi empaj')
                || str_contains($seed, 'empay2')
                || str_contains($seed, 'empay 2')
                || (str_contains($seed, 'empay') && str_contains($seed, '2.0'))
            )
        ) {
            return self::PALETTE[2];
        }

        if ($seed !== '' && preg_match('/\bfm\b/i', $seed) === 1) {
            return self::PALETTE[3];
        }

        if ($seed !== '' && str_contains($seed, 'empay')) {
            return self::PALETTE[0];
        }

        $index = abs(crc32($seed !== '' ? $seed : $fallback)) % count(self::PALETTE);

        return self::PALETTE[$index];
    }

    /**
     * @param  array{name:string,hex:string,rgb:string}  $color
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,strong_bg:string,border:string}
     */
    private static function buildColor(array $color): array
    {
        return [
            'name' => $color['name'],
            'hex' => $color['hex'],
            'rgb' => $color['rgb'],
            'soft_bg' => sprintf('rgba(%s, 0.13)', $color['rgb']),
            'strong_bg' => sprintf('rgba(%s, 0.22)', $color['rgb']),
            'border' => sprintf('rgba(%s, 0.32)', $color['rgb']),
        ];
    }
}
