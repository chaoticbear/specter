<?php
namespace Specter;

use \PDO;

class DB
{
    protected $cons = [];

    public function add($dbName, $dsn, $user = null, $pass = null)
    {
        $this->cons[$dbName] = [
            'dsn'=>$dsn,
            'user'=>$user,
            'pass'=> $pass
        ];
    }

    public function pdo($dbName = 'db')
    {
        $prefix = 'specter_dbh_';
        if (!isset($GLOBALS[$prefix.$dbName])) {
            $GLOBALS[$prefix.$dbName] = new PDO(
                $this->cons[$dbName]['dsn'],
                $this->cons[$dbName]['user'],
                $this->cons[$dbName]['pass']
            );
        }
        return $GLOBALS[$prefix.$dbName];
    }
}
