<?php
namespace App\Models;

use App\Core\Database;
use Throwable;

final class PhotoPack
{
    public static function forSet(int $setId,bool $onlyActive=true):array
    {
        if($setId<1)return [];try{$sql='SELECT * FROM photo_pack_options WHERE set_id=?';if($onlyActive)$sql.=' AND active=1';$sql.=' ORDER BY quantity,slot';$s=Database::db()->prepare($sql);$s->execute([$setId]);return $s->fetchAll();}catch(Throwable){return [];}
    }
    public static function matching(int $setId,int $quantity):?array
    {
        try{$s=Database::db()->prepare('SELECT * FROM photo_pack_options WHERE set_id=? AND quantity=? AND active=1 ORDER BY slot LIMIT 1');$s->execute([$setId,$quantity]);return $s->fetch()?:null;}catch(Throwable){return null;}
    }
    public static function saveOptions(int $setId,array $options):void
    {
        $db=Database::db();$db->prepare('DELETE FROM photo_pack_options WHERE set_id=?')->execute([$setId]);$q=$db->prepare('INSERT INTO photo_pack_options(set_id,slot,quantity,price,active) VALUES(?,?,?,?,?)');
        foreach([1,2,3] as $slot){$o=$options[$slot]??[];$q->execute([$setId,$slot,max(2,(int)($o['quantity']??($slot*5))),max(0,(int)($o['price']??0)),!empty($o['active'])?1:0]);}
    }
    public static function fromPost():array
    {
        $result=[];foreach([1,2,3] as $slot)$result[$slot]=['active'=>isset($_POST['pack_active'][$slot]),'quantity'=>max(2,(int)($_POST['pack_quantity'][$slot]??$slot*5)),'price'=>max(0,(int)($_POST['pack_price'][$slot]??0))];return $result;
    }
}
