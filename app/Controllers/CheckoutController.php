<?php
namespace App\Controllers;
use App\Core\Database;use App\Models\Photo;
final class CheckoutController{
 public function index():void{$photos=Photo::ids($_SESSION['cart']??[]);view('store/checkout',compact('photos'));}
 public function process():never{verify_csrf();$photos=Photo::ids($_SESSION['cart']??[]);if(!$photos)redirect('/carrito');$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);if(!$email){$_SESSION['error']='Ingresa un correo válido';redirect('/checkout');}$total=array_sum(array_column($photos,'price'));$db=Database::db();$db->beginTransaction();$token=bin2hex(random_bytes(24));$s=$db->prepare("INSERT INTO orders(customer_name,customer_email,total,status,download_token,paid_at) VALUES(?,?,?,'paid',?,NOW())");$s->execute([trim($_POST['name']??'Cliente'),$email,$total,$token]);$oid=(int)$db->lastInsertId();$i=$db->prepare('INSERT INTO order_items(order_id,photo_id,unit_price) VALUES(?,?,?)');foreach($photos as $p)$i->execute([$oid,$p['id'],$p['price']]);$db->commit();$_SESSION['cart']=[];redirect('/gracias?token='.$token);}
 public function thanks():void{$token=$_GET['token']??'';$s=Database::db()->prepare("SELECT o.*,p.id photo_id,p.title FROM orders o JOIN order_items i ON i.order_id=o.id JOIN photos p ON p.id=i.photo_id WHERE o.download_token=? AND o.status='paid'");$s->execute([$token]);$items=$s->fetchAll();view('store/thanks',compact('items','token'));}
}

