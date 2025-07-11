<?php
// ===========================================================
// File: helpers.php
// Purpose: Common helper functions for the application.
// ===========================================================

/**
 * Render an emoji icon wrapped in a span element.
 *
 * @param string $key  Key of the icon in the icon map.
 * @param string $size CSS font-size for the emoji. Defaults to '1.2rem'.
 *
 * @return string HTML span with the emoji or an empty string if not found.
 */
function render_icon(string $key, string $size = '1.2rem'): string
{
    $icons = include __DIR__ . '/iconos.php';

    if (!isset($icons[$key])) {
        return '';
    }

    $emoji = $icons[$key];
    return "<span style=\"font-size: {$size}; vertical-align: middle;\">{$emoji}</span>";
}
