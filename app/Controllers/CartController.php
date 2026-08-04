<?php
namespace App\Controllers;
use App\Models\Photo;
final class CartController{public function index():void{$photos=Photo::ids($_SESSION['cart']??[]);view('store/cart',compact('photos'));}public function add():never{verify_csrf();$id=(int)($_POST['id']??0);if(Photo::find($id))$_SESSION['cart']=array_values(array_unique([...($_SESSION['cart']??[]),$id]));redirect('/carrito');}public function remove():never{verify_csrf();$id=(int)($_POST['id']??0);$_SESSION['cart']=array_values(array_filter($_SESSION['cart']??[],fn($x)=>$x!==$id));redirect('/carrito');}}

