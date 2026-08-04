<?php
namespace App\Controllers;use App\Models\Photo;use App\Models\PhotoSet;use App\Models\ShopCart;
final class CartController{
 public function index():void{$items=ShopCart::items();view('store/cart',['items'=>$items,'pageTitle'=>'Carrito | Ultra','flowPage'=>true,'bodyClass'=>'inner','toplineLeft'=>'COMPRA SEGURA','toplineRight'=>'ENTREGA DIGITAL']);}
 public function add():never{verify_csrf();$type=($_POST['type']??'photo')==='set'?'set':'photo';$id=(int)($_POST['id']??0);$valid=$type==='set'?PhotoSet::find($id):Photo::find($id);if($valid)ShopCart::add($type,$id);if($this->wantsJson())$this->json();redirect('/carrito');}
 public function remove():never{verify_csrf();ShopCart::remove((string)($_POST['key']??'photo:'.(int)($_POST['id']??0)));if($this->wantsJson())$this->json();redirect('/carrito');}
 private function wantsJson():bool{return str_contains($_SERVER['HTTP_ACCEPT']??'','application/json')||($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='XMLHttpRequest';}
 private function json():never{$items=ShopCart::items();$result=[];foreach($items as $i)$result[]=['key'=>$i['key'],'type'=>$i['type'],'title'=>$i['title'],'event_name'=>$i['event_name'],'price'=>$i['price'],'price_formatted'=>money($i['price']),'image'=>preview_url(['id'=>$i['cover_id']??$i['photo_id'],'preview_path'=>$i['preview_path']]),'meta'=>$i['type']==='set'?($i['photos_count'].' fotografías · Set completo'):'Fotografía individual'];header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode(['ok'=>true,'count'=>count($result),'total'=>array_sum(array_column($items,'price')),'total_formatted'=>money((int)array_sum(array_column($items,'price'))),'items'=>$result],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
}
