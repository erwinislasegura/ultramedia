<?php
$packOptions=!empty($photo['set_id'])?\App\Models\PhotoPack::forSet((int)$photo['set_id']):[];
if(!empty($photo['set_id'])&&(!empty($photo['individual_enabled'])||$packOptions)):
?>
<form class="smart-photo-selection" method="post" action="<?=url('carrito/agregar-seleccion')?>" data-individual-enabled="<?=!empty($photo['individual_enabled'])?'1':'0'?>">
 <input type="hidden" name="_token" value="<?=csrf()?>">
 <input type="hidden" name="set_id" value="<?=(int)$photo['set_id']?>">
 <input type="hidden" name="return_photo_id" value="<?=(int)$photo['id']?>">

 <div class="selection-header">
  <div><span>ARMA TU COMPRA</span><h2>ELIGE TUS FOTOGRAFÍAS</h2></div>
  <strong class="smart-total"><?=money((int)$photo['price'])?></strong>
 </div>

 <?php if($packOptions):?>
 <div class="available-packs">
  <?php foreach($packOptions as $n=>$option):?>
  <div data-pack-quantity="<?=(int)$option['quantity']?>" data-pack-price="<?=(int)$option['price']?>">
   <small>PACK <?=$n+1?></small><b><?=(int)$option['quantity']?> FOTOS</b><strong><?=money((int)$option['price'])?></strong>
  </div>
  <?php endforeach;?>
 </div>
 <?php endif;?>

 <div class="smart-status">
  <span><b class="smart-count">1</b> seleccionada(s)</span>
  <em class="smart-mode"><?=!empty($photo['individual_enabled'])?'VALOR INDIVIDUAL':'ELIGE UN PACK'?></em>
 </div>

 <div class="mobile-photo-picker">
  <div class="mobile-picker-image">
   <img id="mobileSelectionImage" src="<?=preview_url($photo)?>" alt="<?=htmlspecialchars($photo['title'])?>">
   <span aria-hidden="true">ULTRA</span>
   <div><small id="mobileSelectionPosition">FOTO 1 DE <?=count($related)?></small><strong id="mobileSelectionTitle"><?=htmlspecialchars($photo['title'])?></strong></div>
  </div>
  <div class="mobile-picker-summary">
   <span><small>SELECCIÓN</small><b><strong class="mobile-picker-count">1</strong> <?=count($related)===1?'FOTO':'FOTOS'?></b></span>
   <em class="mobile-picker-mode"><?=!empty($photo['individual_enabled'])?'VALOR INDIVIDUAL':'ELIGE UN PACK'?></em>
   <span class="mobile-picker-price"><small>TOTAL</small><b class="mobile-picker-total"><?=money((int)$photo['price'])?></b></span>
  </div>
  <button class="mobile-picker-submit" type="submit">AGREGAR SELECCIÓN AL CARRITO →</button>
 </div>

 <div class="selector-preview-hint"><span>SELECCIÓN DE FOTOS</span>Marca las imágenes que deseas comprar; el total se actualiza automáticamente.</div>
 <div class="photo-selector smart-selector">
  <?php foreach($related as $n=>$r):?>
  <label class="photo-choice <?=((int)$r['id']===(int)$photo['id'])?'is-viewing':''?>"
         data-preview-image="<?=preview_url($r)?>"
         data-preview-title="<?=htmlspecialchars($r['title'])?>"
         data-photo-index="<?=$n+1?>"
         tabindex="0">
   <input type="checkbox" name="ids[]" value="<?=$r['id']?>" data-price="<?=$r['price']?>" <?=((int)$r['id']===(int)$photo['id'])?'checked':''?>>
   <span><img src="<?=preview_url($r)?>" alt="<?=htmlspecialchars($r['title'])?>"><i>✓</i></span>
   <small><?=htmlspecialchars($r['title'])?></small>
   <b><?=money((int)$r['price'])?></b>
  </label>
  <?php endforeach;?>
 </div>

 <p class="smart-help"><?=$packOptions?'El precio del pack se aplicará automáticamente al alcanzar una de las cantidades indicadas.':'Las fotografías se calcularán según su valor individual.'?></p>
 <button class="desktop-selection-submit" type="submit">AGREGAR SELECCIÓN AL CARRITO →</button>
</form>
<?php endif;?>
