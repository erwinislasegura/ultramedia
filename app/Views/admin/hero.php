<?php
$defaults=['eyebrow'=>'TU ESFUERZO. TU MOMENTO.','title'=>'ENCUENTRA TU','highlight'=>'MEJOR FOTO.','description'=>'Compra una fotografía específica o descarga el set completo de tu participación.','search_placeholder'=>'Número de competidor o evento…','button_text'=>'BUSCAR MIS FOTOS','background_url'=>'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1900&q=90','background_position'=>'center center','overlay_opacity'=>75,'trust_one'=>'▧ Calidad profesional','trust_two'=>'↓ Foto o set completo','trust_three'=>'◇ Pago seguro Flow'];
$hero=array_merge($defaults,$hero);
?>
<div class="content">
 <section class="title"><div><span class="eyebrow">PORTADA</span><h1>HERO <em>PRINCIPAL.</em></h1><p>Administra el mensaje, buscador y apariencia de la primera sección de la tienda.</p></div></section>
 <?php if(!empty($_SESSION['success'])):?><div class="flash success"><?=htmlspecialchars($_SESSION['success']);unset($_SESSION['success']);?></div><?php endif;?>
 <?php if(!empty($_SESSION['error'])):?><div class="flash error"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div><?php endif;?>
 <div class="hero-admin-grid">
  <form class="panel hero-form" method="post" enctype="multipart/form-data" id="heroForm"><input type="hidden" name="_token" value="<?=csrf()?>">
   <label>ETIQUETA SUPERIOR<input name="eyebrow" maxlength="120" value="<?=htmlspecialchars($hero['eyebrow'])?>"></label>
   <div class="two-fields"><label>TÍTULO PRINCIPAL<input name="title" maxlength="180" required value="<?=htmlspecialchars($hero['title'])?>"></label><label>TEXTO DESTACADO<input name="highlight" maxlength="120" required value="<?=htmlspecialchars($hero['highlight'])?>"></label></div>
   <label>DESCRIPCIÓN<textarea name="description" maxlength="500"><?=htmlspecialchars($hero['description'])?></textarea></label>
   <div class="two-fields"><label>PLACEHOLDER DEL BUSCADOR<input name="search_placeholder" maxlength="160" value="<?=htmlspecialchars($hero['search_placeholder'])?>"></label><label>TEXTO DEL BOTÓN<input name="button_text" maxlength="80" value="<?=htmlspecialchars($hero['button_text'])?>"></label></div>
   <label>IMAGEN DE FONDO<input type="file" name="background_image" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WebP. Si no eliges un archivo, se conservará la URL o ruta inferior.</small></label>
   <label>URL O RUTA ACTUAL<input type="text" name="background_url" maxlength="500" required value="<?=htmlspecialchars($hero['background_url'])?>"></label>
   <div class="two-fields"><label>POSICIÓN DE LA IMAGEN<select name="background_position"><?php foreach(['center center'=>'Centro','center top'=>'Superior','center bottom'=>'Inferior','left center'=>'Izquierda','right center'=>'Derecha'] as $value=>$label):?><option value="<?=$value?>" <?=$hero['background_position']===$value?'selected':''?>><?=$label?></option><?php endforeach;?></select></label><label>OSCURIDAD <output id="opacityValue"><?=(int)$hero['overlay_opacity']?>%</output><input type="range" name="overlay_opacity" min="20" max="95" value="<?=(int)$hero['overlay_opacity']?>"></label></div>
   <div class="trust-fields"><label>BENEFICIO 1<input name="trust_one" maxlength="100" value="<?=htmlspecialchars($hero['trust_one'])?>"></label><label>BENEFICIO 2<input name="trust_two" maxlength="100" value="<?=htmlspecialchars($hero['trust_two'])?>"></label><label>BENEFICIO 3<input name="trust_three" maxlength="100" value="<?=htmlspecialchars($hero['trust_three'])?>"></label></div>
   <button>GUARDAR HERO →</button>
  </form>
  <aside class="hero-admin-preview" id="heroPreview" data-base-url="<?=url()?>"><div><span data-preview="eyebrow"></span><h2><b data-preview="title"></b><em data-preview="highlight"></em></h2><p data-preview="description"></p><i><span data-preview="search_placeholder"></span><b data-preview="button_text"></b></i><small><span data-preview="trust_one"></span><span data-preview="trust_two"></span><span data-preview="trust_three"></span></small></div></aside>
 </div>
</div>
<script src="<?=url('assets/admin-hero.js')?>"></script>
