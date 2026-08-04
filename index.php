<?php
declare(strict_types=1);
session_start();
require __DIR__.'/app/Core/bootstrap.php';
use App\Core\Router;
$router=new Router();
foreach([
 ['GET','/admin/login','AuthController@login'],['POST','/admin/login','AuthController@authenticate'],['POST','/admin/logout','AuthController@logout'],
 ['GET','/','StoreController@index'],['GET','/foto','StoreController@photo'],['GET','/preguntas-frecuentes','StoreController@faq'],
 ['GET','/carrito','CartController@index'],['POST','/carrito/agregar','CartController@add'],['POST','/carrito/eliminar','CartController@remove'],
 ['GET','/checkout','CheckoutController@index'],['POST','/checkout','CheckoutController@process'],['GET','/gracias','CheckoutController@thanks'],
 ['GET','/descarga','DownloadController@file'],['GET','/admin','AdminController@dashboard'],['GET','/admin/fotos','AdminController@photos'],
 ['POST','/admin/fotos','AdminController@upload'],['POST','/admin/fotos/descarga','AdminController@toggleDownload'],
 ['GET','/admin/usuarios','UserController@index'],['POST','/admin/usuarios/guardar','UserController@save'],
 ['POST','/admin/usuarios/eliminar','UserController@delete'],['POST','/admin/roles/guardar','UserController@saveRole'],
 ['GET','/admin/pedidos','OrderController@index'],['POST','/admin/pedidos/estado','OrderController@status'],
 ['GET','/admin/cta','CtaController@index'],['POST','/admin/cta','CtaController@save']
] as [$m,$p,$a]) $m==='GET'?$router->get($p,$a):$router->post($p,$a);
$router->dispatch($_SERVER['REQUEST_METHOD'],parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
