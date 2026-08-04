<?php
namespace App\Controllers;
use App\Models\User;
final class AuthController{public function login():void{if(admin_user())redirect('/admin');view('admin/login',['pageTitle'=>'Acceso administrativo','hideChrome'=>true,'bodyClass'=>'admin-login-body','adminLogin'=>true]);}public function authenticate():never{verify_csrf();$user=User::authenticate(trim($_POST['email']??''),$_POST['password']??'');if(!$user){$_SESSION['error']='Correo o contraseña incorrectos.';redirect('/admin/login');}session_regenerate_id(true);unset($user['password_hash']);$_SESSION['admin_user']=$user;redirect('/admin');}public function logout():never{verify_csrf();unset($_SESSION['admin_user']);session_regenerate_id(true);redirect('/admin/login');}}

