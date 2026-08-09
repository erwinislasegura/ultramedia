<?php
namespace App\Models;use App\Core\Database;
final class PhotoSet{
 public static function ensureFeaturedHomeColumn():bool{static $ready=null;if($ready!==null)return $ready;$db=Database::db();try{$db->query('SELECT featured_home FROM photo_sets LIMIT 0');return $ready=true;}catch(\Throwable $e){}try{$db->exec('ALTER TABLE photo_sets ADD COLUMN featured_home TINYINT(1) NOT NULL DEFAULT 0 AFTER set_enabled');return $ready=true;}catch(\Throwable $e){return $ready=false;}}
 public static function find(int $id):?array{$db=Database::db();$coverExpr='MIN(p.id)';try{$db->query('SELECT cover_photo_id FROM photo_sets LIMIT 0');$coverExpr='COALESCE(MAX(CASE WHEN p.id=ps.cover_photo_id THEN p.id END),MIN(p.id))';}catch(\Throwable $e){}$s=$db->prepare("SELECT ps.*,e.name event_name,COUNT(p.id) photos_count,$coverExpr cover_id,MIN(p.preview_path) preview_path FROM photo_sets ps JOIN events e ON e.id=ps.event_id JOIN photos p ON p.set_id=ps.id WHERE ps.id=? AND ps.status='active' GROUP BY ps.id");$s->execute([$id]);return $s->fetch()?:null;}
 public static function photos(int $id):array{$s=Database::db()->prepare("SELECT * FROM photos WHERE set_id=? ORDER BY id");$s->execute([$id]);return $s->fetchAll();}
}
