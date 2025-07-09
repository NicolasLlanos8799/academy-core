<?php
function render_icon($role, $alt = '', $size = 24) {
    static $icons = null;
    if ($icons === null) {
        $icons = include __DIR__ . '/iconos.php';
    }
    if (!isset($icons[$role])) {
        return '';
    }
    $src = $icons[$role];
    $altAttr = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
    $sizeAttr = intval($size);
    return "<img src=\"{$src}\" alt=\"{$altAttr}\" width=\"{$sizeAttr}\" height=\"{$sizeAttr}\" style=\"vertical-align:middle;\">";
}
