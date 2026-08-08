<?php
namespace App\Services;

final class MailTransport
{
    public function send(string $to,string $subject,string $html):bool
    {
        $settings=EmailSettings::smtp();
        if($settings&&!empty($settings['active']))return $this->smtp($settings,$to,$subject,$html);
        $mail=config('mail')?:[];$from=$mail['from_email']??'no-reply@ultramedia.cl';$name=$mail['from_name']??'Ultra Media Digital';
        $headers=['MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8','From: '.$this->encoded($name).' <'.$from.'>','Reply-To: '.($mail['reply_to']??$from),'X-Mailer: Ultra Media Digital'];
        return @mail($to,$this->encoded($subject),$html,implode("\r\n",$headers));
    }
    private function smtp(array $s,string $to,string $subject,string $html):bool
    {
        $host=(string)$s['host'];$port=(int)$s['port'];$encryption=(string)$s['encryption'];$target=($encryption==='ssl'?'ssl://':'').$host;
        $socket=@fsockopen($target,$port,$errno,$error,15);if(!$socket)throw new \RuntimeException('No fue posible conectar con SMTP: '.$error.' ('.$errno.')');stream_set_timeout($socket,15);
        try{
            $this->expect($socket,[220]);$domain=parse_url(url(),PHP_URL_HOST)?:'localhost';$this->command($socket,'EHLO '.$domain,[250]);
            if($encryption==='tls'){$this->command($socket,'STARTTLS',[220]);if(!stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT))throw new \RuntimeException('El servidor no permitió iniciar TLS.');$this->command($socket,'EHLO '.$domain,[250]);}
            if((string)$s['username']!==''){$this->command($socket,'AUTH LOGIN',[334]);$this->command($socket,base64_encode((string)$s['username']),[334]);$this->command($socket,base64_encode((string)$s['password']),[235]);}
            $this->command($socket,'MAIL FROM:<'.$s['from_email'].'>',[250]);$this->command($socket,'RCPT TO:<'.$to.'>',[250,251]);$this->command($socket,'DATA',[354]);
            $headers=['Date: '.date(DATE_RFC2822),'To: <'.$to.'>','From: '.$this->encoded((string)$s['from_name']).' <'.$s['from_email'].'>','Reply-To: '.((string)$s['reply_to']?:$s['from_email']),'Subject: '.$this->encoded($subject),'MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8','Content-Transfer-Encoding: 8bit'];
            $payload=implode("\r\n",$headers)."\r\n\r\n".preg_replace('/(?m)^\./','..',str_replace(["\r\n","\r"],"\n",$html));fwrite($socket,str_replace("\n","\r\n",$payload)."\r\n.\r\n");$this->expect($socket,[250]);$this->command($socket,'QUIT',[221]);fclose($socket);return true;
        }catch(\Throwable $e){fclose($socket);throw $e;}
    }
    private function command($socket,string $command,array $codes):string{fwrite($socket,$command."\r\n");return $this->expect($socket,$codes);}
    private function expect($socket,array $codes):string{$response='';do{$line=fgets($socket,515);if($line===false)throw new \RuntimeException('El servidor SMTP cerró la conexión.');$response.=$line;}while(strlen($line)>3&&$line[3]==='-');$code=(int)substr($response,0,3);if(!in_array($code,$codes,true))throw new \RuntimeException('SMTP respondió: '.trim($response));return $response;}
    private function encoded(string $value):string{return '=?UTF-8?B?'.base64_encode($value).'?=';}
}
