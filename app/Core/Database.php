<?php
namespace App\Core;
use PDO;
final class Database{private static ?PDO $pdo=null;public static function db():PDO{if(self::$pdo)return self::$pdo;$c=config('db');return self::$pdo=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}",$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}}

