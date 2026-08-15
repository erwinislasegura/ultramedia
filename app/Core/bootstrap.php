<?php
declare(strict_types=1);
define('ROOT',dirname(__DIR__,2));
spl_autoload_register(function(string $class):void{$prefix='App\\';if(!str_starts_with($class,$prefix))return;$file=ROOT.'/app/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';if(is_file($file))require $file;});
function config(?string $key=null):mixed{static $c;$c??=require ROOT.'/config/config.php';return $key?($c[$key]??null):$c;}
function view(string $name,array $data=[]):void{extract($data);$view=ROOT.'/app/Views/'.$name.'.php';require ROOT.'/app/Views/layouts/store.php';}
function admin_view(string $name,array $data=[]):void{extract($data);$view=ROOT.'/app/Views/'.$name.'.php';require ROOT.'/app/Views/layouts/admin.php';}
function url(string $path=''):string{
 if(preg_match('~^https?://~',$path))return $path;
 $configured=rtrim((string)config('app_url'),'/');
 if($configured!=='')$base=$configured;
 else{$https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';$host=$_SERVER['HTTP_HOST']??'localhost';$folder=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/'));$base=($https?'https':'http').'://'.$host.($folder==='/'?'':rtrim($folder,'/'));}
 return rtrim($base,'/').'/'.ltrim($path,'/');
}
function media(string $path):string{return preg_match('~^https?://~',$path)?$path:url($path);}
function preview_url(array $photo):string{$path=(string)($photo['preview_path']??'');if(preg_match('~^https?://~',$path))return $path;$id=(int)($photo['id']??$photo['photo_id']??0);return $id?url('vista-previa?id='.$id):media($path);}
function redirect(string $path):never{header('Location: '.url($path));exit;}
function money(int $n):string{return '$'.number_format($n,0,',','.');}
function csrf():string{$_SESSION['csrf']??=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['_token']??'')){http_response_code(419);exit('Sesión expirada');}}
function admin_user():?array{return $_SESSION['admin_user']??null;}
function require_admin():void{
 if(!admin_user())redirect('/admin/login');
 $access=\App\Models\User::accessSnapshot((int)(admin_user()['id']??0));
 if(!$access){unset($_SESSION['admin_user']);redirect('/admin/login');}
 $_SESSION['admin_user']=array_merge($_SESSION['admin_user'],$access);
}
function admin_permissions():array{
 $raw=admin_user()['permissions']??[];
 if(is_string($raw))$raw=json_decode($raw,true);
 return is_array($raw)?array_values(array_filter($raw,'is_string')):[];
}
function admin_can(string $permission):bool{return (admin_user()['role_slug']??'')==='administrador'||in_array($permission,admin_permissions(),true);}
function admin_can_any(array $permissions):bool{foreach($permissions as $permission)if(admin_can($permission))return true;return false;}
function admin_start_path():?string{
 foreach(['dashboard.view'=>'/admin','events.manage'=>'/admin/eventos','photos.manage'=>'/admin/fotos','orders.manage'=>'/admin/pedidos','homepage.manage'=>'/admin/hero','payments.manage'=>'/admin/flow','email.manage'=>'/admin/correo','users.manage'=>'/admin/usuarios','roles.manage'=>'/admin/usuarios'] as $permission=>$path)if(admin_can($permission))return $path;
 return null;
}
function admin_forbidden():never{http_response_code(403);echo '<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso restringido</title><body style="margin:0;display:grid;place-items:center;min-height:100vh;background:#0b1011;color:#fff;font-family:Arial,sans-serif"><main style="max-width:520px;padding:40px"><b style="color:#baff18">ACCESO RESTRINGIDO</b><h1>Tu rol no permite acceder a este módulo.</h1><p style="color:#aeb6b5;line-height:1.6">Solicita a un administrador que habilite el permiso correspondiente.</p><a href="'.url(ltrim(admin_start_path()??'admin/login','/')).'" style="display:inline-block;margin-top:12px;padding:14px 18px;background:#baff18;color:#111;text-decoration:none;font-weight:bold">VOLVER AL PANEL</a></main></body></html>';exit;}
function customer_user():?array{return $_SESSION['customer_user']??null;}
function require_customer():void{if(!customer_user())redirect('/mi-cuenta/login');}
