<?php
namespace Specter;

use Specter\App;
use Specter\View;

class Controller
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    protected function render($file, array $vars = []) {
        $view = new View($this->app);
        return $view->read($file, $vars);
    }
}
