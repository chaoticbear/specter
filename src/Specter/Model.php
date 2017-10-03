<?php
namespace Specter;

use Specter\DB;

abstract class Model
{
    protected static $con = 'db';
    protected static $tbl;
    protected static $pk = 'id';
    protected $db;
    protected $mods = [];
    protected $rs = [];

    protected static function quote($str)
    {
        switch(DB::type(static::$con)) {
            case 'sqlsql':
            case 'mssql':
            case 'dblib':
                return '[' . $str . ']';
                break;
            case 'mysql':
                return '`' . $str . '`';
                break;
            default:
                return '"' . $str . '"';
                break;
        }
    }

    public static function tbl()
    {
        return static::quote(static::$tbl);
    }

    public static function pk()
    {
        return static::quote(static::$pk);
    }


    public static function cols()
    {
        foreach (static::pdo(static::$con)
            ->query('SHOW COLUMNS FROM ' . static::tbl()) as $rw) {
            var_dump($rw);
        }
    }

    public static function one($id)
    {
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('SELECT * FROM ' . static::tbl() . ' WHERE ' .
            static::pk() . ' = ?');
        $stm->execute([$id]);
        return $stm->fetchObject(static::class);
    }

    public static function del($id)
    {
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('DELETE FROM ' . static::tbl() . ' WHERE '. 
            static::pk() . ' = ?');
        $stm->execute([$id]);
        return $stm->rowCount();
    }

    public function __construct()
    {
        $this->db = DB::pdo(static::$con);
        $this->mods = [];
    }

    public function get($key)
    {
        return $this->rs[$key];
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function set($key, $val)
    {
        $this->mods[$key] = $val;
        $this->rs[$key] = $val;
        return $this;
    }

    public function __set($key, $val)
    {
        return $this->set($key, $val);
    }

    public function delete()
    {
        $pk = static::$pk;
        return static::del($this->$pk);
    }

    public function save()
    {
        if (!empty($this->mods)) {
            $s = '';
            foreach ($this->mods as $k => $v) {
                $s .= ',' . static::quote($k) . '=?';
            }
            $s = substr($s,1);
            $s = 'UPDATE ' . static::tbl() . ' SET ' . $s . ' WHERE ' .
                static::pk() . ' = ?';
            $p = [];
            foreach ($this->mods as $k => $v) {
                $p[] = $v;
            }
            $pk = static::$pk;
            $p[] = $this->$pk;
            $stm = $this->db->prepare($s);
            $stm->execute($p);
            $this->mods = [];
            return $stm->rowCount();
        }
        $this->mods = [];
        return 0;
    }
}
