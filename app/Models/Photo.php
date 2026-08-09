<?php
namespace App\Models;use App\Core\Database;
final class Photo{
 private static function select():string{return "SELECT p.*,e.name event_name,COALESCE(ps.individual_enabled,1) individual_enabled,COALESCE(ps.set_enabled,0) set_enabled,COALESCE(ps.pack_enabled,0) pack_enabled,ps.pack_quantity,ps.pack_price,ps.set_price,ps.name set_name FROM photos p JOIN events e ON e.id=p.event_id LEFT JOIN photo_sets ps ON ps.id=p.set_id";}
 private static function publicWhere():string{return "p.status='active' AND e.status='published' AND (ps.id IS NULL OR ps.status='active')";}
 public static function all(string $q=''):array{$db=Database::db();$sql=self::select().' WHERE '.self::publicWhere();$args=[];if($q!==''){$sql.=' AND (p.bib_number LIKE ? OR e.name LIKE ? OR ps.name LIKE ?)';$args=["%$q%","%$q%","%$q%"];} $sql.=' ORDER BY p.id DESC LIMIT 48';$s=$db->prepare($sql);$s->execute($args);return $s->fetchAll();}
 public static function find(int $id):?array{$s=Database::db()->prepare(self::select().' WHERE p.id=? AND '.self::publicWhere());$s->execute([$id]);return $s->fetch()?:null;}
 public static function ids(array $ids):array{if(!$ids)return[];$marks=implode(',',array_fill(0,count($ids),'?'));$s=Database::db()->prepare(self::select().' WHERE '.self::publicWhere()." AND p.id IN ($marks)");$s->execute($ids);return $s->fetchAll();}
}
