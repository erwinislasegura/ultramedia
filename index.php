<?php
declare(strict_types=1);
session_start();
require __DIR__.'/app/Core/bootstrap.php';
use App\Core\Router;
$router=new Router();
foreach([
 ['GET','/admin/login','AuthController@login'],['POST','/admin/login','AuthController@authenticate'],['POST','/admin/logout','AuthController@logout'],
 ['GET','/','StoreController@index'],['GET','/foto','StoreController@photo'],['GET','/preguntas-frecuentes','StoreController@faq'],
 ['GET','/mi-cuenta/login','CustomerController@login'],['POST','/mi-cuenta/login','CustomerController@authenticate'],['POST','/mi-cuenta/salir','CustomerController@logout'],['GET','/mi-cuenta','CustomerController@dashboard'],['GET','/mi-cuenta/pedido','CustomerController@order'],['POST','/mi-cuenta/clave','CustomerController@password'],
 ['GET','/carrito','CartController@index'],['POST','/carrito/agregar','CartController@add'],['POST','/carrito/agregar-varias','CartController@addMany'],['POST','/carrito/agregar-pack','CartController@addPack'],['POST','/carrito/eliminar','CartController@remove'],
 ['GET','/checkout','CheckoutController@index'],['POST','/checkout','CheckoutController@process'],['POST','/pago/flow/confirmacion','CheckoutController@confirmation'],['POST','/pago/flow/retorno','CheckoutController@paymentReturn'],['GET','/pago/flow/retorno','CheckoutController@paymentReturn'],['GET','/pago/resultado','CheckoutController@result'],['GET','/gracias','CheckoutController@thanks'],
 ['GET','/descarga','DownloadController@file'],['GET','/descarga-set','DownloadController@set'],['GET','/descarga-pack','DownloadController@pack'],['GET','/mi-cuenta/descarga','DownloadController@customerFile'],['GET','/vista-previa','PreviewController@file'],['GET','/admin','AdminController@dashboard'],['GET','/admin/fotos','AdminController@photos'],
 ['POST','/admin/fotos','AdminController@upload'],['POST','/admin/fotos/descarga','AdminController@toggleDownload'],
 ['GET','/admin/fotos/editar','AdminController@editPhoto'],['POST','/admin/fotos/editar','AdminController@updatePhoto'],
 ['POST','/admin/fotos/estado','AdminController@togglePhotoStatus'],['POST','/admin/fotos/eliminar','AdminController@deletePhoto'],
 ['GET','/admin/usuarios','UserController@index'],['POST','/admin/usuarios/guardar','UserController@save'],
 ['POST','/admin/usuarios/eliminar','UserController@delete'],['POST','/admin/roles/guardar','UserController@saveRole'],
 ['GET','/admin/pedidos','OrderController@index'],['POST','/admin/pedidos/estado','OrderController@status'],
 ['GET','/admin/eventos','EventController@index'],['POST','/admin/eventos/guardar','EventController@save'],['POST','/admin/eventos/estado','EventController@status'],['POST','/admin/eventos/eliminar','EventController@delete'],
 ['GET','/admin/cta','CtaController@index'],['POST','/admin/cta','CtaController@save'],['GET','/admin/hero','HeroController@index'],['POST','/admin/hero','HeroController@save'],['GET','/admin/flow','FlowSettingsController@index'],['POST','/admin/flow','FlowSettingsController@save']
] as [$m,$p,$a]) $m==='GET'?$router->get($p,$a):$router->post($p,$a);
$router->dispatch($_SERVER['REQUEST_METHOD'],parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
