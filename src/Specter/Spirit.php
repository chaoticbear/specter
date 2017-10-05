<?php
namespace Specter;

use Specter\Specter;
use Specter\Apparition;

abstract class Spirit
{
    protected $specter;
    protected $params = [];

    public function __construct(Specter $specter, $params = [])
    {
        $this->specter = $specter;
        $this->params = $params;
    }

    protected function render($file, array $vars = []) {
        $apparition = new Apparition($this->specter);
        return $apparition->appear($file, $vars);
    }
}
