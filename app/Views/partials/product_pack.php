<?php if(!empty($photo['pack_enabled']) && (int)$photo['pack_quantity']>=2 && count($related)>=(int)$photo['pack_quantity']):?>
<form class="photo-pack-box" method="post" action="<?=url('carrito/agregar-pack')?>" data-pack-limit="<?=(int)$photo['pack_quantity']?>">
 <input type="hidden" name="_token" value="<?=csrf()?>">
 <input type="hidden" name="set_id" value="<?=(int)$photo['set_id']?>">
 <input type="hidden" name="return_photo_id" value="<?=(int)$photo['id']?>">
 <header><span>PACK PERSONALIZADO</span><strong><?=money((int)$photo['pack_price'])?></strong></header>
 <h2>ELIGE <?=(int)$photo['pack_quantity']?> FOTOGRAFÍAS</h2>
 <p>Arma tu propio pack seleccionando exactamente <?=(int)$photo['pack_quantity']?> imágenes de este set.</p>
 <div class="pack-progress"><i><b></b></i><span><strong class="pack-selected-count">0</strong> / <?=(int)$photo['pack_quantity']?> seleccionadas</span></div>
 <div class="pack-photo-selector">
  <?php foreach($related as $r):?><label><input type="checkbox" name="ids[]" value="<?=$r['id']?>"><span><img src="<?=preview_url($r)?>" alt="<?=htmlspecialchars($r['title'])?>"><i>✓</i></span><small><?=htmlspecialchars($r['title'])?></small></label><?php endforeach;?>
 </div>
 <button type="submit" disabled>SELECCIONA <?=(int)$photo['pack_quantity']?> FOTOS</button>
</form>
<?php endif;?>
