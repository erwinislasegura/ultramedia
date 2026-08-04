<?php
namespace App\Models;
final class ShopCart{
 public static function keys():array{return array_values(array_unique(array_map(fn($v)=>is_numeric($v)?'photo:'.$v:(string)$v,$_SESSION['cart']??[])));}
 public static function add(string $type,int $id):void{if(!in_array($type,['photo','set'],true)||$id<1)return;$keys=self::keys();$key=$type.':'.$id;if(!in_array($key,$keys,true))$keys[]=$key;$_SESSION['cart']=$keys;}
 public static function remove(string $key):void{$_SESSION['cart']=array_values(array_filter(self::keys(),fn($v)=>$v!==$key));}
 public static function items():array{$items=[];foreach(self::keys() as $key){[$type,$id]=array_pad(explode(':',$key,2),2,0);if($type==='photo'){$p=Photo::find((int)$id);if($p&&($p['individual_enabled']??1))$items[]=['key'=>$key,'type'=>'photo','id'=>(int)$p['id'],'photo_id'=>(int)$p['id'],'set_id'=>null,'title'=>$p['title'],'event_name'=>$p['event_name'],'price'=>(int)$p['price'],'preview_path'=>$p['preview_path']];}else{$s=PhotoSet::find((int)$id);if($s&&$s['set_enabled'])$items[]=['key'=>$key,'type'=>'set','id'=>(int)$s['id'],'photo_id'=>null,'set_id'=>(int)$s['id'],'title'=>$s['name'],'event_name'=>$s['event_name'],'price'=>(int)$s['set_price'],'preview_path'=>$s['preview_path'],'cover_id'=>$s['cover_id'],'photos_count'=>$s['photos_count']];}}return $items;}
}
