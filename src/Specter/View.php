<?php
namespace Specter;

use Specter\Specter;

class View
{
    protected $specter;
    protected $viewPath;

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
        $this->viewPath = $specter->get('viewPath');
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
