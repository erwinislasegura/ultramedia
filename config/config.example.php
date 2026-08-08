<?php
return [
 'app_url'=>'https://tudominio.cl',
 'db'=>['host'=>'localhost','port'=>'3306','name'=>'ultramedia','user'=>'usuario','pass'=>'clave','charset'=>'utf8mb4'],
 'currency'=>'CLP',
 'mail'=>[
  'from_email'=>'no-reply@tudominio.cl',
  'from_name'=>'Ultra Media Digital',
  'reply_to'=>'contacto@tudominio.cl',
 ],
 'flow'=>[
  'api_key'=>'TU_API_KEY_FLOW',
  'secret_key'=>'TU_SECRET_KEY_FLOW',
  'api_url'=>'https://sandbox.flow.cl/api', // Producción: https://www.flow.cl/api
  'http_timeout'=>10,
  'order_timeout'=>1800,
 ],
 'demo_admin'=>true,
];
