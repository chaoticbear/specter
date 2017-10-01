<?php
namespace Specter;

use Specter\App;

class View
{
    protected $app;
    protected $viewPath;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->viewPath = $this->app->get('viewPath');
    }

    public function read($file='', $vars='')
    {
        if (is_array($vars)) {
            extract($vars);
        }
        ob_start();
        require($this->viewPath . $file);
        return ob_get_clean();
    }
}
