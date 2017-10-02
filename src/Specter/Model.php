<?php
namespace Specter;

//TODO this will become something simpler using only PDO
// https://phpdelusions.net/pdo/objects
abstract class Model
{
    protected $specter;

    protected $con = 'db';
    protected $db;
    protected $tbl;
    protected $pk = 'id';
    protected $mods = [];

    public function __construct(Specter $specter)
    {
        $this->specter = $specter;
        $this->db = $specter->db->pdo($this->con);
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function __set($key, $val)
    {
        $this->mods[$key] = $val;
        $this->$key = $val;
        return $this;
    }

    public static function one($id)
    {
        $stm = $this->db->prepare('SELECT * FROM ? WHERE ? = ?');
        $stm->execute([self::tbl, self::pk, $id]);
        return $stm->fetchObject(get_class($this));
    }

    public static function del($id)
    {
        $stm = $this->db->prepare('DELETE FROM ? WHERE ? = ?');
        $stm->execute([self::tbl, self::pk, $id]);
        return $stm->rowCount();
    }

    public function delete()
    {
        self::del($this->$$this->pk);
    }

    public function save()
    {
        if (!empty($this->mods)) {
            $s = '';
            foreach ($this->mods as $k => $v) {
                $s .= ',?=?';
            }
            $s = substr($s,1);
            $s = 'UPDATE ? SET ' . $c . ' WHERE ? = ?';
            $p = [];
            $p[] = $this->tbl;
            foreach ($this->mods as $k => $v) {
                $p[] = $k;
                $p[] = $v;
            }
            $p[] = $this->pk;
            $p[] = $this->$$this->pk;
            $stm = $this->db->prepare($s);
            $stm->execute($p);
            $this->mods = [];
            return $stm->rowCount();
        }
        $this->mods = [];
        return 0;
    }
}
