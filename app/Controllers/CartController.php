<?php
namespace App\Controllers;use App\Models\Photo;use App\Models\PhotoSet;use App\Models\ShopCart;
final class CartController{
 public function index():void{$items=ShopCart::items();view('store/cart',['items'=>$items,'pageTitle'=>'Carrito | Ultra','flowPage'=>true,'bodyClass'=>'inner','toplineLeft'=>'COMPRA SEGURA','toplineRight'=>'ENTREGA DIGITAL']);}
 public function add():never{verify_csrf();$type=($_POST['type']??'photo')==='set'?'set':'photo';$id=(int)($_POST['id']??0);$valid=$type==='set'?PhotoSet::find($id):Photo::find($id);if($valid)ShopCart::add($type,$id);redirect('/carrito');}
 public function remove():never{verify_csrf();ShopCart::remove((string)($_POST['key']??'photo:'.(int)($_POST['id']??0)));redirect('/carrito');}
}
