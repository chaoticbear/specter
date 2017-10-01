<?php
namespace Specter;

use Specter\Specter;
use Specter\View;

class Controller
{
    protected $specter;

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
    }

    protected function render($file, array $vars = []) {
        $view = new View($this->specter);
        return $view->read($file, $vars);
    }
}
