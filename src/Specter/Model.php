<?php
namespace Specter;

//TODO this will become something simpler using only PDO
// https://phpdelusions.net/pdo/objects
class Model
{
    protected $specter;

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
    }
}
