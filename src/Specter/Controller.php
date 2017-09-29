<?php
namespace Specter;

use Specter\App;
use Specter\View;

abstract class Controller
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    protected function render($view, array $vars = []) {
        $view = new View($this->app);
        return $view->read($view, $vars);
    }
}
