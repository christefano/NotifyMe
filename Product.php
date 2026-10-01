<?php

namespace Kanboard\Plugin\NotifyMe;

class Product
{
    private static $values = null;

    /** Kanboard's own wording, used when config.php omits a key. */
    private static $defaults = array(
        'product_name' => 'Kanboard',
        'product_url'  => '',
    );

    /** NotKanboard's strings class. When it's installed, its names win, so the name is set in one place. */
    const NOTKANBOARD_CLASS = '\Kanboard\Plugin\NotKanboard\Strings';

    public static function get($key)
    {
        if (self::$values === null) {
            self::$values = self::load();
        }

        return isset(self::$values[$key]) && is_scalar(self::$values[$key]) ? (string) self::$values[$key] : '';
    }

    private static function load()
    {
        if (class_exists(self::NOTKANBOARD_CLASS)) {
            $strings = self::NOTKANBOARD_CLASS;

            return array(
                'product_name' => $strings::get('footer_name'),
                'product_url'  => $strings::get('product_url'),
            );
        }

        $values = self::$defaults;
        $file = __DIR__.'/config.php';

        if (is_file($file)) {
            $custom = include $file;

            if (is_array($custom)) {
                $values = array_merge($values, $custom);
            }
        }

        // Optional override in Kanboard's data directory, which an in-app plugin update doesn't replace.
        // JSON and not PHP, so a writable data directory is never a code-execution path.
        $override = defined('DATA_DIR') ? DATA_DIR.'/NotifyMe.config.json' : '';

        if ($override !== '' && is_file($override)) {
            $custom = json_decode((string) file_get_contents($override), true);

            if (is_array($custom)) {
                $values = array_merge($values, $custom);
            }
        }

        return $values;
    }

    /** Footer line for every notification email, with the name linked when a URL is set. */
    public static function footer()
    {
        $name = htmlspecialchars(self::get('product_name'), ENT_QUOTES, 'UTF-8');
        $url = self::get('product_url');

        if (preg_match('#^https?://#i', $url)) {
            $name = '<a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">'.$name.'</a>';
        }

        // t() escapes its arguments, so pass a placeholder and substitute the markup afterward.
        return str_replace('@@NAME@@', $name, t('This is an automated notification from %s.', '@@NAME@@'));
    }
}
