<?php
namespace Specter;

//TODO this will become something simpler using only PDO
// https://phpdelusions.net/pdo/objects
abstract class Model
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
}
