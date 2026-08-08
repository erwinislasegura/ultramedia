<?php
namespace App\Services;

use App\Core\Database;

final class OrderMailer
{
    public function sendPaid(int $orderId): bool
    {
        $db=Database::db();
        $claim=$db->prepare("UPDATE orders SET confirmation_email_sent_at=NOW(),download_expires_at=COALESCE(download_expires_at,DATE_ADD(paid_at,INTERVAL 15 DAY)) WHERE id=? AND status='paid' AND paid_at IS NOT NULL AND confirmation_email_sent_at IS NULL");
        $claim->execute([$orderId]);
        if($claim->rowCount()!==1)return true;
        $s=$db->prepare('SELECT * FROM orders WHERE id=?');$s->execute([$orderId]);$order=$s->fetch();
        if(!$order||!filter_var($order['customer_email'],FILTER_VALIDATE_EMAIL)){$this->release($orderId);return false;}
        $s=$db->prepare("SELECT i.*,COALESCE(p.title,i.item_title,ps.name) title FROM order_items i LEFT JOIN photos p ON p.id=i.photo_id LEFT JOIN photo_sets ps ON ps.id=i.set_id WHERE i.order_id=? ORDER BY i.id");$s->execute([$orderId]);$items=$s->fetchAll();
        $subject='Tus fotografías Ultra están listas · Pedido UMD-'.str_pad((string)$orderId,4,'0',STR_PAD_LEFT);
        $html=$this->html($order,$items);
        $mail=config('mail')?:[];$fromEmail=$mail['from_email']??'no-reply@ultramedia.cl';$fromName=$mail['from_name']??'Ultra Media Digital';
        $headers=['MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8','From: '.$this->header($fromName).' <'.$fromEmail.'>','Reply-To: '.($mail['reply_to']??$fromEmail),'X-Mailer: Ultra Media Digital'];
        $sent=@mail($order['customer_email'],'=?UTF-8?B?'.base64_encode($subject).'?=',$html,implode("\r\n",$headers));
        if(!$sent){$this->release($orderId);error_log('Ultra email: no fue posible enviar el pedido '.$orderId);}
        return $sent;
    }

    private function html(array $order,array $items):string
    {
        $name=htmlspecialchars($order['customer_name']?:'Cliente',ENT_QUOTES,'UTF-8');
        $token=urlencode($order['download_token']);$download=htmlspecialchars(url('gracias?token='.$token),ENT_QUOTES,'UTF-8');
        $logo=htmlspecialchars(url('assets/ultra-logo.png'),ENT_QUOTES,'UTF-8');
        $expires=strtotime((string)$order['download_expires_at']);$expiresText=$expires?date('d/m/Y \a \l\a\s H:i',$expires):date('d/m/Y',strtotime('+15 days'));
        $rows='';foreach($items as $item){$type=$item['item_type']==='set'?'SET COMPLETO':($item['item_type']==='pack'?'PACK PERSONALIZADO':'FOTOGRAFÍA INDIVIDUAL');$rows.='<tr><td style="padding:15px 0;border-bottom:1px solid #e6e9e8"><strong style="display:block;color:#111718;font-size:14px">'.htmlspecialchars($item['title'],ENT_QUOTES,'UTF-8').'</strong><span style="color:#7a8384;font-size:11px;letter-spacing:.05em">'.$type.'</span></td><td style="padding:15px 0;border-bottom:1px solid #e6e9e8;text-align:right;color:#111718;font-weight:700">'.money((int)$item['unit_price']).'</td></tr>';}
        $orderCode='UMD-'.str_pad((string)$order['id'],4,'0',STR_PAD_LEFT);
        return '<!doctype html><html lang="es"><head><meta charset="utf-8"></head><body style="margin:0;background:#eef1f0;font-family:Arial,Helvetica,sans-serif;color:#121718"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f0;padding:28px 12px"><tr><td align="center"><table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;background:#fff"><tr><td style="background:#080d0e;padding:25px 34px;border-top:5px solid #baff18"><img src="'.$logo.'" width="165" alt="Ultra Media Digital" style="display:block;max-width:165px"><p style="margin:18px 0 0;color:#baff18;font-size:10px;font-weight:700;letter-spacing:.16em">PAGO CONFIRMADO · '.$orderCode.'</p></td></tr><tr><td style="padding:38px 34px 24px"><h1 style="margin:0;color:#0b1011;font-size:34px;line-height:1.05">¡TU COMPRA ESTÁ LISTA!</h1><p style="margin:17px 0 0;color:#697273;font-size:14px;line-height:1.65">Hola '.$name.', Flow confirmó correctamente tu pago. Ya puedes descargar tus fotografías originales, sin marca de agua y en alta resolución.</p><div style="margin:28px 0;background:#baff18;padding:20px 22px"><strong style="display:block;color:#0b1011;font-size:16px">IMPORTANTE: EL ENLACE VENCE EN 15 DÍAS</strong><span style="display:block;margin-top:7px;color:#263000;font-size:12px;line-height:1.5">Disponible hasta el <b>'.$expiresText.'</b>. Descarga y guarda tus archivos antes de esa fecha; después el enlace dejará de funcionar.</span></div><div style="text-align:center;margin:30px 0"><a href="'.$download.'" style="display:inline-block;background:#0a0f10;color:#fff;text-decoration:none;padding:17px 27px;font-size:12px;font-weight:700;letter-spacing:.06em">DESCARGAR MI COMPRA →</a></div><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td colspan="2" style="padding:15px 0 6px;color:#7b8485;font-size:10px;font-weight:700;letter-spacing:.13em">DETALLE DEL PEDIDO</td></tr>'.$rows.'<tr><td style="padding:20px 0;font-size:14px;font-weight:700">TOTAL PAGADO</td><td style="padding:20px 0;text-align:right;font-size:20px;font-weight:800">'.money((int)$order['total']).'</td></tr></table><p style="margin:8px 0;color:#879091;font-size:11px;line-height:1.6">Si el botón no funciona, copia este enlace en tu navegador:<br><a href="'.$download.'" style="color:#536000;word-break:break-all">'.$download.'</a></p></td></tr><tr><td style="background:#0a0f10;padding:24px 34px;color:#8d9697;font-size:10px;line-height:1.6">ULTRA MEDIA DIGITAL · FOTOGRAFÍA DEPORTIVA<br>Este correo fue enviado automáticamente después de la validación de Flow.</td></tr></table></td></tr></table></body></html>';
    }
    private function release(int $id):void{Database::db()->prepare('UPDATE orders SET confirmation_email_sent_at=NULL WHERE id=?')->execute([$id]);}
    private function header(string $value):string{return '=?UTF-8?B?'.base64_encode($value).'?=';}
}
