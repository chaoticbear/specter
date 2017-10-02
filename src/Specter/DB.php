<?php
namespace Specter;

use Specter\Specter;
use \PDO;

class DB
{
    protected $prefix = 'specter_dbh_';
    protected $specter;
    protected $cons = [];

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
        $this->settings($specter->get('dbs'));
    }

    public function settings($dbs = [])
    {
        foreach($dbs as $dbName => $dbConf) {
            $this->add($dbName, $dbConf['dsn'], $dbConf['user'], $dbConf['pass']);
        }
    }

    public function add($dbName, $dsn, $user = null, $pass = null)
    {
        $this->cons[$dbName] = [
            'dsn'=>$dsn,
            'user'=>$user,
            'pass'=> $pass
        ];
        if (!isset($GLOBALS[$this->prefix.$dbName])) {
            $GLOBALS[$this->prefix.$dbName] = new PDO(
                $this->cons[$dbName]['dsn'],
                $this->cons[$dbName]['user'],
                $this->cons[$dbName]['pass']
            );
        }
    }

    public static function pdo($dbName = 'db')
    {
        return $GLOBALS[self::$prefix.$dbName];
    }
}
