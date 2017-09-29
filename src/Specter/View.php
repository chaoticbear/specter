<?php
namespace Specter;

abstract class View
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
}
