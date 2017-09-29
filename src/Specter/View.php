<?php
namespace Specter;

use Specter\App;

abstract class View
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function read($file='', $vars='')
    {
        if (is_array($vars)) {
            extract($vars);
        }
        ob_start();
        require($this->app->get('viewPath') . $file);
        return ob_get_clean();
    }
}
