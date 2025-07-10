<?php
function render_icon($key, $size = '1.2rem') {
  $icons = include __DIR__ . '/iconos.php';
  if (!isset($icons[$key])) return '';
  $emoji = $icons[$key];
  return "<span style='font-size: $size; vertical-align: middle;'>$emoji</span>";
}
