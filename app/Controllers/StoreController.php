<?php
namespace App\Controllers;
use App\Models\Photo;
final class StoreController{public function index():void{$photos=Photo::all(trim($_GET['q']??''));view('store/index',compact('photos'));}public function photo():void{$photo=Photo::find((int)($_GET['id']??0));if(!$photo){http_response_code(404);exit('Foto no encontrada');}view('store/photo',compact('photo'));}}

