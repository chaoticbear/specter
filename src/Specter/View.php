<?php
namespace Specter;

abstract class SoulView
{
    static function read($file='', $vars='')
    {
        if (is_array($vars)) {
            extract($vars);
        }
        ob_start();
        require(VIEW_PATH . $file);
        return ob_get_clean();
    }

    static function render($file='', $vars='')
    {
        if (is_array($vars)) {
            extract($vars);
        }
        require(VIEW_PATH . $file);
    }
}
