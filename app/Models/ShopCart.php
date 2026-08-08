<?php
namespace App\Models;

final class ShopCart
{
    public static function keys(): array
    {
        return array_values(array_unique(array_map(fn($v)=>is_numeric($v)?'photo:'.$v:(string)$v, $_SESSION['cart']??[])));
    }

    public static function add(string $type, int $id): void
    {
        if (!in_array($type, ['photo','set'], true) || $id < 1) return;
        $keys=self::keys();
        $key=$type.':'.$id;
        if (!in_array($key,$keys,true)) $keys[]=$key;
        $_SESSION['cart']=$keys;
    }

    public static function addPack(int $setId, array $photoIds): void
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',$photoIds))));
        sort($ids,SORT_NUMERIC);
        if ($setId<1 || !$ids) return;
        $keys=self::keys();
        // Un set puede tener un solo pack activo; una nueva selección reemplaza la anterior.
        $keys=array_values(array_filter($keys,fn($key)=>!str_starts_with($key,'pack:'.$setId.':')));
        $keys[]='pack:'.$setId.':'.implode(',',$ids);
        $_SESSION['cart']=$keys;
    }

    public static function remove(string $key): void
    {
        $_SESSION['cart']=array_values(array_filter(self::keys(),fn($v)=>$v!==$key));
    }

    public static function items(): array
    {
        $items=[];
        foreach(self::keys() as $key){
            [$type,$id,$selection]=array_pad(explode(':',$key,3),3,'');
            if($type==='photo'){
                $p=Photo::find((int)$id);
                if($p&&($p['individual_enabled']??1))$items[]=['key'=>$key,'type'=>'photo','id'=>(int)$p['id'],'photo_id'=>(int)$p['id'],'set_id'=>null,'selected_photo_ids'=>[],'title'=>$p['title'],'event_name'=>$p['event_name'],'price'=>(int)$p['price'],'preview_path'=>$p['preview_path']];
                continue;
            }
            $set=PhotoSet::find((int)$id);
            if(!$set)continue;
            if($type==='set'&&$set['set_enabled']){
                $items[]=['key'=>$key,'type'=>'set','id'=>(int)$set['id'],'photo_id'=>null,'set_id'=>(int)$set['id'],'selected_photo_ids'=>[],'title'=>$set['name'],'event_name'=>$set['event_name'],'price'=>(int)$set['set_price'],'preview_path'=>$set['preview_path'],'cover_id'=>$set['cover_id'],'photos_count'=>$set['photos_count']];
                continue;
            }
            if($type==='pack'&&!empty($set['pack_enabled'])){
                $ids=array_values(array_unique(array_filter(array_map('intval',explode(',',$selection)))));
                $photos=Photo::ids($ids);
                $validIds=array_map('intval',array_column(array_filter($photos,fn($p)=>(int)$p['set_id']===(int)$set['id']),'id'));
                sort($ids,SORT_NUMERIC);sort($validIds,SORT_NUMERIC);
                if(count($ids)!==(int)$set['pack_quantity']||$ids!==$validIds)continue;
                $cover=$photos[0]??null;
                $items[]=['key'=>$key,'type'=>'pack','id'=>(int)$set['id'],'photo_id'=>null,'set_id'=>(int)$set['id'],'selected_photo_ids'=>$ids,'title'=>'Pack de '.count($ids).' fotos · '.$set['name'],'event_name'=>$set['event_name'],'price'=>(int)$set['pack_price'],'preview_path'=>$cover['preview_path']??$set['preview_path'],'cover_id'=>$cover['id']??$set['cover_id'],'photos_count'=>count($ids)];
            }
        }
        return $items;
    }
}
