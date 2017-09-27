<?php
namespace Specter;

//TODO this will become something simpler using only PDO
// https://phpdelusions.net/pdo/objects
abstract class SoulModel
{
    protected $pkname;
    protected $tablename;
    protected $dbhfnname;
    protected $QUOTE_STYLE='MYSQL'; // valid types are MYSQL,MSSQL,ANSI
    protected $COMPRESS_ARRAY=true;
    public $rs = array(); // for holding all object property variables
}
