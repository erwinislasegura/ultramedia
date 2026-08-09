<?php
namespace App\Models;use App\Core\Database;
final class PhotoSet{
 public static function find(int $id):?array{$s=Database::db()->prepare("SELECT ps.*,e.name event_name,COUNT(p.id) photos_count,MIN(p.id) cover_id,MIN(p.preview_path) preview_path FROM photo_sets ps JOIN events e ON e.id=ps.event_id JOIN photos p ON p.set_id=ps.id AND p.status='active' WHERE ps.id=? AND ps.status='active' AND e.status='published' AND (ps.individual_enabled=1 OR ps.set_enabled=1 OR ps.pack_enabled=1) GROUP BY ps.id");$s->execute([$id]);return $s->fetch()?:null;}
 public static function photos(int $id):array{$s=Database::db()->prepare("SELECT * FROM photos WHERE set_id=? AND status='active' ORDER BY id");$s->execute([$id]);return $s->fetchAll();}
}
