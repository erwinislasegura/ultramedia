<?php
namespace App\Controllers;

use App\Models\Photo;
use App\Models\PhotoPack;
use App\Models\PhotoSet;
use App\Models\ShopCart;

final class CartController
{
    public function index():void{$items=ShopCart::items();view('store/cart',['items'=>$items,'pageTitle'=>'Carrito | Ultra','flowPage'=>true,'bodyClass'=>'inner','toplineLeft'=>'COMPRA SEGURA','toplineRight'=>'ENTREGA DIGITAL']);}
    public function add():never{verify_csrf();$type=($_POST['type']??'photo')==='set'?'set':'photo';$id=(int)($_POST['id']??0);$valid=$type==='set'?PhotoSet::find($id):Photo::find($id);if($valid)ShopCart::add($type,$id);if($this->wantsJson())$this->json();redirect('/carrito');}
    public function addMany():never{verify_csrf();$ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['ids']??[])))));foreach($ids as $id){$photo=Photo::find($id);if($photo&&($photo['individual_enabled']??1))ShopCart::add('photo',$id);}if($this->wantsJson())$this->json();redirect('/carrito');}
    public function addPack():never
    {
        verify_csrf();
        $setId=(int)($_POST['set_id']??0);
        $ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['ids']??[])))));
        $set=PhotoSet::find($setId);
        $photos=Photo::ids($ids);
        $valid=$set&&PhotoPack::matching($setId,count($ids))&&count($photos)===count($ids);
        foreach($photos as $photo)if((int)$photo['set_id']!==$setId)$valid=false;
        if($valid)ShopCart::addPack($setId,$ids);else $_SESSION['error']='La cantidad seleccionada no corresponde a un pack activo.';
        if($this->wantsJson())$this->json($valid);
        redirect($valid?'/carrito':'/foto?id='.(int)($_POST['return_photo_id']??0));
    }
    public function addSelection():never
    {
        verify_csrf();$setId=(int)($_POST['set_id']??0);$ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['ids']??[])))));$photos=Photo::ids($ids);$set=PhotoSet::find($setId);$valid=$set&&$ids&&count($photos)===count($ids);foreach($photos as $p)if((int)$p['set_id']!==$setId)$valid=false;
        if(!$valid){$_SESSION['error']='La selección de fotografías no es válida.';redirect('/foto?id='.(int)($_POST['return_photo_id']??0));}
        $pack=PhotoPack::matching($setId,count($ids));
        if($pack)ShopCart::addPack($setId,$ids);
        elseif(!empty($set['individual_enabled']))foreach($ids as $id)ShopCart::add('photo',$id);
        else{$_SESSION['error']='Selecciona una cantidad correspondiente a uno de los packs disponibles.';redirect('/foto?id='.(int)($_POST['return_photo_id']??0));}
        if($this->wantsJson())$this->json();redirect('/carrito');
    }
    public function remove():never{verify_csrf();ShopCart::remove((string)($_POST['key']??'photo:'.(int)($_POST['id']??0)));if($this->wantsJson())$this->json();redirect('/carrito');}
    private function wantsJson():bool{return str_contains($_SERVER['HTTP_ACCEPT']??'','application/json')||($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='XMLHttpRequest';}
    private function json(bool $ok=true):never{$items=ShopCart::items();$result=[];foreach($items as $i)$result[]=['key'=>$i['key'],'type'=>$i['type'],'title'=>$i['title'],'event_name'=>$i['event_name'],'price'=>$i['price'],'price_formatted'=>money($i['price']),'image'=>preview_url(['id'=>$i['cover_id']??$i['photo_id'],'preview_path'=>$i['preview_path']]),'meta'=>$i['type']==='set'?($i['photos_count'].' fotografías · Set completo'):($i['type']==='pack'?($i['photos_count'].' fotografías · Pack personalizado'):'Fotografía individual')];header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode(['ok'=>$ok,'count'=>count($result),'total'=>array_sum(array_column($items,'price')),'total_formatted'=>money((int)array_sum(array_column($items,'price'))),'items'=>$result],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
}
