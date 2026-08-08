<?php
namespace App\Controllers;

use App\Core\Database;
use App\Services\EmailSettings;
use App\Services\MailTransport;
use App\Services\PaymentSettings;
use Throwable;

final class EmailSettingsController
{
    public function index():void{$settings=EmailSettings::smtp()?:['active'=>0,'host'=>'','port'=>587,'encryption'=>'tls','username'=>'','from_email'=>'','from_name'=>'Ultra Media Digital','reply_to'=>''];admin_view('admin/email-settings',['settings'=>$settings,'pageTitle'=>'Correo SMTP','adminSection'=>'email']);}
    public function save():never
    {
        verify_csrf();$host=trim($_POST['host']??'');$port=max(1,min(65535,(int)($_POST['port']??587)));$encryption=in_array($_POST['encryption']??'tls',['tls','ssl','none'],true)?$_POST['encryption']:'tls';$username=trim($_POST['username']??'');$password=(string)($_POST['password']??'');$fromEmail=filter_var($_POST['from_email']??'',FILTER_VALIDATE_EMAIL);$replyTo=filter_var($_POST['reply_to']??'',FILTER_VALIDATE_EMAIL);$current=EmailSettings::smtp();
        if($host===''||!$fromEmail||!$replyTo||($password===''&&!$current)){$_SESSION['error']='Completa el servidor SMTP, remitente, respuesta y contraseña.';redirect('/admin/correo');}
        try{$encrypted=$password!==''?PaymentSettings::encrypt($password):(string)$current['password_encrypted'];$sql="INSERT INTO email_settings(id,active,host,port,encryption,username,password_encrypted,from_email,from_name,reply_to) VALUES(1,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE active=VALUES(active),host=VALUES(host),port=VALUES(port),encryption=VALUES(encryption),username=VALUES(username),password_encrypted=VALUES(password_encrypted),from_email=VALUES(from_email),from_name=VALUES(from_name),reply_to=VALUES(reply_to)";Database::db()->prepare($sql)->execute([isset($_POST['active'])?1:0,$host,$port,$encryption,$username,$encrypted,$fromEmail,trim($_POST['from_name']??'Ultra Media Digital'),$replyTo]);
            if(($_POST['action']??'save')==='test'){$test=filter_var($_POST['test_email']??'',FILTER_VALIDATE_EMAIL);if(!$test)throw new \RuntimeException('Ingresa un correo válido para la prueba.');if(!isset($_POST['active']))throw new \RuntimeException('Activa SMTP antes de realizar la prueba.');$html='<div style="font-family:Arial;background:#eef1f0;padding:30px"><div style="max-width:560px;margin:auto;background:#fff;border-top:5px solid #baff18"><div style="background:#090d0e;color:#fff;padding:28px"><h1 style="margin:0">ULTRA MEDIA DIGITAL</h1><p style="color:#baff18">CONFIGURACIÓN SMTP CORRECTA</p></div><div style="padding:30px;color:#333"><h2>Correo de prueba enviado</h2><p>La conexión SMTP quedó configurada correctamente. Los pedidos pagados y las notificaciones podrán enviarse desde esta cuenta.</p></div></div></div>';if(!(new MailTransport())->send($test,'Prueba de correo · Ultra Media Digital',$html))throw new \RuntimeException('El servidor no confirmó el envío.');$_SESSION['success']='Configuración guardada y correo de prueba enviado a '.$test.'.';}else $_SESSION['success']='Configuración SMTP guardada correctamente.';
        }catch(Throwable $e){error_log('SMTP settings: '.$e->getMessage());$_SESSION['error']='No fue posible completar la operación: '.$e->getMessage();}
        redirect('/admin/correo');
    }
}
