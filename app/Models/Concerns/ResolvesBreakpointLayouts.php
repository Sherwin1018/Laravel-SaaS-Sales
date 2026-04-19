<?php

namespace App\Models\Concerns;

trait ResolvesBreakpointLayouts
{
    /**
     * Whether the layout array has builder root or legacy sections.
     */
    public static function layoutArrayHasContent(?array $layout): bool
    {
        if (! is_array($layout)) {
            return false;
        }
        $root = $layout['root'] ?? null;
        if (is_array($root) && count($root) > 0) {
            return true;
        }
        $sections = $layout['sections'] ?? null;

        return is_array($sections) && count($sections) > 0;
    }

    /**
     * Resolved layout for preview/publish: mobile falls back to desktop when unset or empty.
     * Legacy `tablet` requests and stored `layout_json_tablet` resolve through the mobile path.
     *
     * @param  string  $breakpoint  desktop|mobile|tablet (tablet is treated as mobile)
     */
    public function effectiveLayoutForBreakpoint(string $breakpoint = 'desktop'): array
    {
        $breakpoint = strtolower(trim($breakpoint));
        if ($breakpoint === 'tablet') {
            $breakpoint = 'mobile';
        }
        if (! in_array($breakpoint, ['desktop', 'mobile'], true)) {
            $breakpoint = 'desktop';
        }

        $desktop = is_array($this->layout_json ?? null) ? $this->layout_json : [];
        if ($breakpoint === 'desktop') {
            return $desktop;
        }

        $mobile = is_array($this->layout_json_mobile ?? null) ? $this->layout_json_mobile : [];
        if (self::layoutArrayHasContent($mobile)) {
            return $mobile;
        }

        $legacyTablet = is_array($this->layout_json_tablet ?? null) ? $this->layout_json_tablet : [];
        if (self::layoutArrayHasContent($legacyTablet)) {
            return $legacyTablet;
        }

        return $desktop;
    }
}
