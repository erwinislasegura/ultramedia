<?php
namespace App\Services;
use RuntimeException;
final class FlowClient{
 private array $config;
 public function __construct(){
  $this->config=(array)config('flow');$saved=PaymentSettings::flow();if($saved){$this->config['api_key']=$saved['api_key'];$this->config['secret_key']=$saved['secret_key'];$this->config['api_url']=$saved['environment']==='production'?'https://www.flow.cl/api':'https://sandbox.flow.cl/api';$this->config['active']=(bool)$saved['active'];}
  if(!$this->configured())throw new RuntimeException('Flow no está configurado. Define FLOW_API_KEY y FLOW_SECRET_KEY.');
 }
 public function configured():bool{return ($this->config['active']??true)&&trim((string)($this->config['api_key']??''))!==''&&trim((string)($this->config['secret_key']??''))!=='';}
 public function createPayment(array $order):array{
  return $this->request('POST','payment/create',[
   'apiKey'=>$this->config['api_key'],'commerceOrder'=>$order['commerce_order'],'subject'=>$order['subject'],
   'currency'=>'CLP','amount'=>(int)$order['amount'],'email'=>$order['email'],'paymentMethod'=>9,
   'urlConfirmation'=>$order['confirmation_url'],'urlReturn'=>$order['return_url'],
   'optional'=>json_encode(['order_id'=>(int)$order['id']],JSON_UNESCAPED_SLASHES),'timeout'=>(int)($this->config['order_timeout']??1800)
  ]);
 }
 public function getStatus(string $token):array{return $this->request('GET','payment/getStatus',['apiKey'=>$this->config['api_key'],'token'=>$token]);}
 private function request(string $method,string $service,array $params):array{
  if(!function_exists('curl_init'))throw new RuntimeException('La extensión cURL de PHP es obligatoria para procesar pagos.');
  ksort($params,SORT_STRING);$toSign='';foreach($params as $key=>$value)$toSign.=$key.$value;
  $params['s']=hash_hmac('sha256',$toSign,(string)$this->config['secret_key']);$url=rtrim((string)$this->config['api_url'],'/').'/'.$service;
  $ch=curl_init();$options=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>(int)($this->config['http_timeout']??10),CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_HTTPHEADER=>['Accept: application/json']];
  if($method==='POST'){$options[CURLOPT_POST]=true;$options[CURLOPT_POSTFIELDS]=http_build_query($params);$options[CURLOPT_HTTPHEADER][]='Content-Type: application/x-www-form-urlencoded';}else $options[CURLOPT_URL]=$url.'?'.http_build_query($params);
  $options[CURLOPT_URL]??=$url;curl_setopt_array($ch,$options);$body=curl_exec($ch);$error=curl_error($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  if($body===false)throw new RuntimeException('No fue posible conectar con Flow: '.$error);$data=json_decode($body,true);
  if($code!==200||!is_array($data))throw new RuntimeException('Flow rechazó la solicitud'.(is_array($data)&&isset($data['message'])?': '.$data['message']:'.'));
  return $data;
 }
}
