<?php
namespace Specter;

use Specter\Specter;

class Apparition
{
    protected $specter;
    protected $apparitionPath;
    protected $url;
    public $title = null;

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
        $this->apparitionPath = $specter->get('apparitionPath');
        $this->url = $specter->get('url');
    }

    public function appear($file='', $vars='')
    {
        $_file = $file;
        if (is_array($vars)) {
            extract($vars);
        }
        ob_start();
        require($this->apparitionPath . $_file);
        return ob_get_clean();
    }

    protected function url()
    {
        return $this->url;
    }
}
