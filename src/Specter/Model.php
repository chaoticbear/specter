<?php
namespace Specter;

use Specter\DB;

abstract class Model
{
    protected static $con = 'db';
    protected $db;
    protected static $tbl;
    protected static $pk = 'id';
    protected $mods = [];
    protected $rs = [];

    public function __construct()
    {
        $this->db = DB::pdo(static::$con);
        $this->mods = [];
    }

    protected static function quote($str)
    {
        $db = DB::pdo(static::$con);
        switch($db->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
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

    public static function one($id)
    {
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('SELECT * FROM ' . static::quote(static::$tbl) .
            ' WHERE ' . static::quote(static::$pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->fetchObject(static::class);
    }

    public static function del($id)
    {
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('DELETE FROM ' . static::quote(static::$tbl) .
            ' WHERE '. static::quote(static::$pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->rowCount();
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
            $s = 'UPDATE ' . static::quote(static::$tbl) . ' SET ' . $s .
                ' WHERE ' . static::quote(static::$pk) . ' = ?';
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
