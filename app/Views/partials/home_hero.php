<?php
$defaults=['eyebrow'=>'TU ESFUERZO. TU MOMENTO.','title'=>'ENCUENTRA TU','highlight'=>'MEJOR FOTO.','description'=>'Compra una fotografía específica o descarga el set completo de tu participación.','search_placeholder'=>'Número de competidor o evento…','button_text'=>'BUSCAR MIS FOTOS','background_url'=>'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1900&q=90','background_position'=>'center center','overlay_opacity'=>75,'trust_one'=>'▧ Calidad profesional','trust_two'=>'↓ Foto o set completo','trust_three'=>'◇ Pago seguro Flow'];
$hero=array_merge($defaults,is_array($hero??null)?$hero:[]);
$opacity=max(20,min(95,(int)$hero['overlay_opacity']))/100;
$backgroundValue=(string)$hero['background_url'];
$backgroundUrl=preg_match('~^https?://~i',$backgroundValue)
 ? $backgroundValue
 : url('hero-imagen?v='.rawurlencode((string)($hero['updated_at']??'')));
$background=str_replace(["'",'\\'],['%27','/'],$backgroundUrl);
$gradient="linear-gradient(90deg,rgba(8,10,11,".min(.98,$opacity+.18)."),rgba(8,10,11,$opacity) 48%,rgba(8,10,11,".max(.08,$opacity-.55)."))";
?>
<section class="hero">
 <div class="hero-bg" style="background-image:<?=$gradient?>,url('<?=htmlspecialchars($background)?>');background-position:<?=htmlspecialchars($hero['background_position'])?>"></div>
 <div class="hero-copy">
  <span class="eyebrow"><?=htmlspecialchars($hero['eyebrow'])?></span>
  <h1><?=nl2br(htmlspecialchars($hero['title']))?><br><em><?=htmlspecialchars($hero['highlight'])?></em></h1>
  <p><?=htmlspecialchars($hero['description'])?></p>
  <div class="trust"><?php foreach(['trust_one','trust_two','trust_three'] as $item):?><?php if($hero[$item]!==''):?><span><?=htmlspecialchars($hero[$item])?></span><?php endif;?><?php endforeach;?></div>
 </div>
</section>
