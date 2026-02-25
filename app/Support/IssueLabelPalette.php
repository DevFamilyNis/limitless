<?php

declare(strict_types=1);

namespace App\Support;

final class IssueLabelPalette
{
    /**
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,border:string,font_weight:int,border_width:string}
     */
    public static function forStatus(?string $key, ?string $name): array
    {
        return self::resolve('status', $key ?: $name);
    }

    /**
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,border:string,font_weight:int,border_width:string}
     */
    public static function forPriority(?string $key, ?string $name): array
    {
        return self::resolve('priority', $key ?: $name);
    }

    /**
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,border:string,font_weight:int,border_width:string}
     */
    public static function forCategory(?string $name): array
    {
        return self::resolve('category', $name);
    }

    /**
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,border:string,font_weight:int,border_width:string}
     */
    private static function resolve(string $group, ?string $value): array
    {
        /** @var array{default?:string,rules?:array<int, array{needles?:array<int, string>,color?:string,style?:array{bg_alpha?:float,border_alpha?:float,border_width?:string,font_weight?:int}}>} $groupConfig */
        $groupConfig = config("issue-labels.{$group}", []);
        $defaultColor = (string) ($groupConfig['default'] ?? 'slate');
        $normalized = self::normalize($value);

        foreach (($groupConfig['rules'] ?? []) as $rule) {
            $needles = $rule['needles'] ?? [];
            $color = is_string($rule['color'] ?? null) ? $rule['color'] : $defaultColor;
            $style = is_array($rule['style'] ?? null) ? $rule['style'] : [];

            if (self::containsAny($normalized, $needles)) {
                return self::build($color, $defaultColor, $style);
            }
        }

        return self::build($defaultColor, 'slate', []);
    }

    /**
     * @param  array{bg_alpha?:float,border_alpha?:float,border_width?:string,font_weight?:int}  $style
     * @return array{name:string,hex:string,rgb:string,soft_bg:string,border:string,font_weight:int,border_width:string}
     */
    private static function build(string $colorName, string $fallbackColor, array $style): array
    {
        /** @var array<string, array{hex:string,rgb:string}> $tokens */
        $tokens = config('issue-labels.tokens', []);
        $tokenName = array_key_exists($colorName, $tokens) ? $colorName : $fallbackColor;

        if (! array_key_exists($tokenName, $tokens)) {
            $tokenName = 'slate';
            $tokens[$tokenName] = ['hex' => '#64748b', 'rgb' => '100, 116, 139'];
        }

        $token = $tokens[$tokenName];
        $backgroundAlpha = is_float($style['bg_alpha'] ?? null) ? $style['bg_alpha'] : 0.13;
        $borderAlpha = is_float($style['border_alpha'] ?? null) ? $style['border_alpha'] : 0.32;
        $fontWeight = is_int($style['font_weight'] ?? null) ? $style['font_weight'] : 500;
        $borderWidth = is_string($style['border_width'] ?? null) ? $style['border_width'] : '1px';

        return [
            'name' => $tokenName,
            'hex' => $token['hex'],
            'rgb' => $token['rgb'],
            'soft_bg' => sprintf('rgba(%s, %s)', $token['rgb'], (string) $backgroundAlpha),
            'border' => sprintf('rgba(%s, %s)', $token['rgb'], (string) $borderAlpha),
            'font_weight' => $fontWeight,
            'border_width' => $borderWidth,
        ];
    }

    private static function normalize(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        return (string) preg_replace('/[^a-z0-9]+/i', '', mb_strtolower(trim($value)));
    }

    /**
     * @param  array<int, string>  $needles
     */
    private static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, self::normalize($needle))) {
                return true;
            }
        }

        return false;
    }
}
