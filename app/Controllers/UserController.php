<?php
namespace App\Controllers;
use App\Models\User;
final class UserController{
 private const PERMISSIONS=[
  'dashboard.view'=>'Ver dashboard',
  'events.manage'=>'Gestionar eventos',
  'photos.manage'=>'Gestionar fotografías',
  'orders.manage'=>'Gestionar ventas',
  'homepage.manage'=>'Configurar portada',
  'payments.manage'=>'Configurar Flow',
  'email.manage'=>'Configurar correo SMTP',
  'users.manage'=>'Gestionar usuarios',
  'roles.manage'=>'Gestionar roles'
 ];
 public function index():void{$users=User::all();$roles=User::roles();admin_view('admin/users',['users'=>$users,'roles'=>$roles,'permissions'=>self::PERMISSIONS,'pageTitle'=>'Usuarios y roles','adminSection'=>'users']);}
 public function save():never{verify_csrf();$name=trim($_POST['name']??'');$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);$role=(int)($_POST['role_id']??0);$id=(int)($_POST['id']??0);if(!$name||!$email||!$role||(!$id&&strlen($_POST['password']??'')<8)){$_SESSION['error']='Completa los datos. La contraseña nueva debe tener al menos 8 caracteres.';redirect('/admin/usuarios');}try{User::save(['id'=>$id,'name'=>$name,'email'=>$email,'role_id'=>$role,'status'=>in_array($_POST['status']??'',['active','inactive'],true)?$_POST['status']:'active','password'=>$_POST['password']??'']);$_SESSION['success']='Usuario guardado correctamente.';}catch(\Throwable $e){$_SESSION['error']='No se pudo guardar. Revisa que el correo no esté repetido.';}redirect('/admin/usuarios');}
 public function delete():never{verify_csrf();$id=(int)($_POST['id']??0);if($id>1)try{User::delete($id);$_SESSION['success']='Usuario eliminado.';}catch(\Throwable $e){$_SESSION['error']='No fue posible eliminar este usuario.';}redirect('/admin/usuarios');}
 public function saveRole():never{
  verify_csrf();
  $id=(int)($_POST['id']??0);
  $name=trim((string)($_POST['name']??''));
  $requested=is_array($_POST['permissions']??null)?$_POST['permissions']:[];
  $permissions=array_values(array_intersect(array_keys(self::PERMISSIONS),$requested));
  if($name===''||strlen($name)>80){$_SESSION['error']='Escribe un nombre de rol válido de hasta 80 caracteres.';redirect('/admin/usuarios');}
  try{
   $existing=$id?User::role($id):null;
   if(($existing['slug']??'')==='administrador')$permissions=array_keys(self::PERMISSIONS);
   $savedId=User::saveRole(['id'=>$id,'name'=>$name,'permissions'=>$permissions]);
   if((int)(admin_user()['role_id']??0)===$savedId){$_SESSION['admin_user']['role_name']=$name;$_SESSION['admin_user']['permissions']=json_encode($permissions,JSON_UNESCAPED_UNICODE);}
   $_SESSION['success']=$id?'Rol actualizado correctamente.':'Rol creado correctamente.';
  }catch(\Throwable $e){$_SESSION['error']='No se pudo guardar el rol. Revisa que el nombre no esté repetido.';}
  redirect('/admin/usuarios');
 }
 public function deleteRole():never{
  verify_csrf();
  try{User::deleteRole((int)($_POST['id']??0));$_SESSION['success']='Rol eliminado correctamente.';}
  catch(\DomainException $e){$_SESSION['error']=$e->getMessage();}
  catch(\Throwable $e){$_SESSION['error']='No se pudo eliminar el rol.';}
  redirect('/admin/usuarios');
 }
}
