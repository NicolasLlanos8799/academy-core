# surfschool

## Icon System

Common icons across the application are handled through an emoji based helper.

1. `php/iconos.php` maps action names to emojis.
2. `php/helpers.php` provides `render_icon($key, $size = '1.2rem')` to output
   the emoji wrapped in a `<span>`.
3. Include `php/helpers.php` in any PHP file and call `render_icon` where an
   icon is needed.

Example:

```php
require_once __DIR__ . '/php/helpers.php';
echo render_icon('delete');
```
