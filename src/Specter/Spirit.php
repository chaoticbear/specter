<?php
namespace Specter;

use Specter\Specter;
use Specter\Apparition;

abstract class Spirit
{
    protected $specter;
    protected $layout = 'default';
    protected $params = [];

    public function __construct(Specter $specter, $params = [])
    {
        $this->specter = $specter;
        $this->params = $params;
    }

    protected function render($file, array $vars = []) {
        $r = '';
        $apparition = new Apparition($this->specter);
        $r .= $apparition->appear('layouts/' . $this->layout . '/header.php',
            $vars);
        $r .= $apparition->appear($file, $vars);
        $r .= $apparition->appear('layouts/' . $this->layout . '/footer.php',
            $vars);
        return $r;
    }
}
