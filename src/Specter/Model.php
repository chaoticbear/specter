<?php
namespace Specter;

use Specter\DB;

abstract class Model
{
    public static $con = 'db';
    public static $tbl;
    public static $pk = 'id';
    public static $one = [];
    public static $many = [];
    public static $belongs = [];
    public static $btm = [];

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
        return static::tbl().'.'.static::quote(static::$pk);
    }

    public static function cols()
    {
        $r = [];
        foreach (DB::pdo(static::$con)
            ->query('SHOW COLUMNS FROM ' . static::tbl()) as $rw) {
            $r[] = ['name'=>$rw['Field']];
        }
        return $r;
    }

    public static function new()
    {
        $class = static::class;
        $r = new $class;
        $cols = static::cols();
        foreach($cols as $col) {
            $r->set($col['name'], null);
        }
        return $r;
    }

    public static function sel($s, $p = [])
    {
        $db = DB::pdo(static::$con);
        $st = $db->prepare($s);
        $st->execute($p);
        $st->setFetchMode(\PDO::FETCH_CLASS, static::class);
        return $st;
    }

    public static function one($id)
    {
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('SELECT * FROM ' . static::tbl() . ' WHERE ' .
            static::pk() . ' = ?');
        $stm->execute([$id]);
        return $stm->fetchObject(static::class);
    }

    public static function del($id, $cascade = true)
    {
        $r = 0;
        if ($cascade === true) {
            $r = static::cascade($id);
        }
        $db = DB::pdo(static::$con);
        $stm = $db->prepare('DELETE FROM ' . static::tbl() . ' WHERE ' .
            static::pk() . ' = ?');
        $stm->execute([$id]);
        $r += $stm->rowCount();
        return $r;
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

    public function delete($cascade = true)
    {
        $pk = static::$pk;
        return static::del($this->$pk, $cascade);
    }

    public function save()
    {
        $r = 0;
        if (!empty($this->mods)) {
            $pk = static::$pk;
            $s = '';
            $id = 0;
            if ($this->$pk === null) {
                $s2 = '';
                $p = [];
                foreach ($this->mods as $k => $v) {
                    if ($this->$pk !== $k) {
                        $s .= ',' . static::quote($k);
                        $s2 .= ',?';
                        $p[] = $v;
                    }
                }
                $s = 'INSERT INTO ' . static::tbl() . ' (' . substr($s,1) .
                    ') VALUES (' . substr($s2,1) . ')';
                $stm = $this->db->prepare($s);
                $stm->execute($p);
                $id = $this->db->lastInsertId();
                $r = $id;
            } else {
                $p = [];
                foreach ($this->mods as $k => $v) {
                    $s .= ',' . static::quote($k) . '=?';
                    $p[] = $v;
                }
                $s = substr($s,1);
                $s = 'UPDATE ' . static::tbl() . ' SET ' . $s . ' WHERE ' .
                    static::pk() . ' = ?';
                $id = $this->$pk;
                $p[] = $this->$pk;
                $stm = $this->db->prepare($s);
                $stm->execute($p);
                $r = $stm->rowCount();
            }
            $s = 'SELECT * FROM ' . static::tbl() . ' WHERE ' . static::pk() .
                ' = ?';
            $st = $this->db->prepare($s);
            $st->setFetchMode( \PDO::FETCH_INTO, $this);
            $st->execute([$id]);
            $st->fetch(\PDO::FETCH_INTO);
        }
        $this->mods = [];
        return $r;
    }

    public function rel($class)
    {
        $pk = static::$pk;
        foreach (static::$belongs as $rc => $rid) {
            if ($class === $rc) {
                if(!empty($this->get($rid))) {
                    return $class::one($this->get($rid));
                } else {
                    return null;
                }
            }
        }
        foreach (static::$many as $rc => $rid) {
            if ($class === $rc) {
                return $class::sel('SELECT * FROM ' . $class::tbl() .
                    ' WHERE ' . static::quote($rid) . ' = ?', [$this->$pk]);
            }
        }
        foreach (static::$one as $rc => $rid) {
            if ($class === $rc) {
                $st = $class::sel('SELECT * FROM ' . $class::tbl() .
                    ' WHERE ' . static::quote($rid) . ' = ?', [$this->$pk]);
                return $st->fetch();
            }
        }
        foreach (static::$btm as $rc => $lc) {
            if ($class === $rc) {
                return $class::sel('SELECT * FROM ' . $rc::tbl() .
                    ' JOIN ' . $lc::tbl() .
                    ' ON ' . $rc::pk() .'='.
                    $lc::tbl().'.'.static::quote($lc::$belongs[$rc]) .
                    ' WHERE ' .
                    $lc::tbl().'.'.static::quote($lc::$belongs[static::class]).
                    ' = ?', [$this->$pk]);
            }
        }
        return null;
    }

    protected static function cascade($id)
    {
        $me = static::one($id);
        $r = 0;
        $db = DB::pdo(static::$con);
        foreach (static::$many as $rc => $rid) {
            $st = $me->rel($rc);
            while ($rel = $st->fetch()) {
                $r += $rel->delete();
            }
        }
        foreach (static::$one as $rc => $rid) {
            $rel = $me->rel($rc);
            if (is_object($rel)) {
                $r += $rel->delete();
            }
        }
        foreach (static::$btm as $rc => $lc) {
            $st = $db->prepare('DELETE FROM ' . $lc::tbl() .
                ' WHERE ' . static::quote($lc::$belongs[static::class]) .
                ' = ?');
            $st->execute([$id]);
            $r += $st->rowCount();
        }
        return $r;
    }
}
