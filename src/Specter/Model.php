<?php
namespace Specter;

use Specter\DB;

abstract class Model
{
    protected $con = 'db';
    protected $db;
    protected $tbl;
    protected $pk = 'id';
    protected $mods = [];
    protected $rs = [];

    public function __construct()
    {
        $this->db = DB::pdo($this->con);
        $this->mods = [];
    }

    protected function quote($str)
    {
        switch($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
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

    public function one($id)
    {
        $stm = $this->db->prepare('SELECT * FROM ' . $this->quote($this->tbl) .
            ' WHERE ' . $this->quote($this->pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->fetchObject(get_class($this));
    }

    public function delete($id = null)
    {
        if ($id === null) {
            $pk = $this->pk;
            $id = $this->$pk;
        }
        $stm = $this->db->prepare('DELETE FROM ' . $this->quote($this->tbl) .
            ' WHERE '. $this->quote($this->pk) . ' = ?');
        $stm->execute([$id]);
        return $stm->rowCount();
    }

    public function save()
    {
        if (!empty($this->mods)) {
            $s = '';
            foreach ($this->mods as $k => $v) {
                $s .= ',' . $this->quote($k) . '=?';
            }
            $s = substr($s,1);
            $s = 'UPDATE ' . $this->quote($this->tbl) . ' SET ' . $s .
                ' WHERE ' . $this->quote($this->pk) . ' = ?';
            $p = [];
            foreach ($this->mods as $k => $v) {
                $p[] = $v;
            }
            $pk = $this->pk;
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
