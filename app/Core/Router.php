<?php
namespace App\Core;
final class Router{
 private array $routes=[];
 public function get(string $p,string $a):void{$this->routes['GET'][$p]=$a;}
 public function post(string $p,string $a):void{$this->routes['POST'][$p]=$a;}
 public function dispatch(string $method,string $uri):void{
  $base=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
  if($base!=='/'&&str_starts_with($uri,$base))$uri=substr($uri,strlen($base));
  $uri='/'.trim($uri,'/');if($uri==='//')$uri='/';
  if(str_starts_with($uri,'/admin')&&$uri!=='/admin/login'){
   require_admin();
   $required=$this->adminPermission($method,$uri);
   if($required!==null&&!admin_can_any((array)$required)){
    if($uri==='/admin'&&($start=admin_start_path())&&$start!=='/admin')redirect($start);
    admin_forbidden();
   }
  }
  $action=$this->routes[$method][$uri]??null;
  if(!$action){http_response_code(404);echo 'Página no encontrada';return;}
  [$class,$fn]=explode('@',$action);$class='App\\Controllers\\'.$class;(new $class)->$fn();
 }
 private function adminPermission(string $method,string $uri):string|array|null{
  if($uri==='/admin')return 'dashboard.view';
  if(str_starts_with($uri,'/admin/fotos'))return 'photos.manage';
  if(str_starts_with($uri,'/admin/eventos'))return 'events.manage';
  if(str_starts_with($uri,'/admin/pedidos'))return 'orders.manage';
  if(str_starts_with($uri,'/admin/hero')||str_starts_with($uri,'/admin/cta'))return 'homepage.manage';
  if(str_starts_with($uri,'/admin/flow'))return 'payments.manage';
  if(str_starts_with($uri,'/admin/correo'))return 'email.manage';
  if(str_starts_with($uri,'/admin/usuarios/'))return 'users.manage';
  if(str_starts_with($uri,'/admin/roles/'))return 'roles.manage';
  if($uri==='/admin/usuarios'&&$method==='GET')return ['users.manage','roles.manage'];
  return null;
 }
}
