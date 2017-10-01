<?php
namespace Specter;

use Specter\Specter;
use Specter\View;

class Controller
{
    protected $specter;
    protected $params = [];

    public function __construct(Specter $specter, $params = [])
    {
        $this->specter = $specter;
        $this->params = $params;
    }

    protected function render($file, array $vars = []) {
        $view = new View($this->specter);
        return $view->read($file, $vars);
    }
}
