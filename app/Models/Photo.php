<?php
namespace App\Models;
use App\Core\Database;
final class Photo{
 public static function all(string $q=''):array{$db=Database::db();if($q!==''){$s=$db->prepare("SELECT p.*,e.name event_name FROM photos p JOIN events e ON e.id=p.event_id WHERE p.status='active' AND (p.bib_number LIKE ? OR e.name LIKE ?) ORDER BY p.id DESC");$s->execute(["%$q%","%$q%"]);return $s->fetchAll();}return $db->query("SELECT p.*,e.name event_name FROM photos p JOIN events e ON e.id=p.event_id WHERE p.status='active' ORDER BY p.id DESC LIMIT 48")->fetchAll();}
 public static function find(int $id):?array{$s=Database::db()->prepare('SELECT p.*,e.name event_name FROM photos p JOIN events e ON e.id=p.event_id WHERE p.id=?');$s->execute([$id]);return $s->fetch()?:null;}
 public static function ids(array $ids):array{if(!$ids)return[];$marks=implode(',',array_fill(0,count($ids),'?'));$s=Database::db()->prepare("SELECT p.*,e.name event_name FROM photos p JOIN events e ON e.id=p.event_id WHERE p.id IN ($marks)");$s->execute($ids);return $s->fetchAll();}
}

