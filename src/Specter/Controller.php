<?php
namespace Specter;

use Specter\App;

abstract class Controller
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
}
