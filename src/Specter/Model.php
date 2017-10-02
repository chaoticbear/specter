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
        $this->db = DB::pdo(self::con);
        $this->mods = [];
    }

    protected static function quote($str)
    {
        $db = DB::pdo(self::con);
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
        $db = DB::pdo(self::con);
        $stm = $db->prepare('SELECT * FROM ' . self::quote(self::tbl) .
            ' WHERE ' . self::quote(self::pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->fetchObject(get_class($this));
    }

    public static function del($id)
    {
        $db = DB::pdo(self::con);
        $stm = $db->prepare('DELETE FROM ' . self::quote(self::tbl) .
            ' WHERE '. self::quote(self::pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->rowCount();
    }

    public function delete()
    {
        $pk = self::pk;
        return self::del($this->$pk);
    }

    public function save()
    {
        if (!empty($this->mods)) {
            $s = '';
            foreach ($this->mods as $k => $v) {
                $s .= ',' . self::quote($k) . '=?';
            }
            $s = substr($s,1);
            $s = 'UPDATE ' . self::quote(self::tbl) . ' SET ' . $s .
                ' WHERE ' . self::quote(self::pk) . ' = ?';
            $p = [];
            foreach ($this->mods as $k => $v) {
                $p[] = $v;
            }
            $pk = self::pk;
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
