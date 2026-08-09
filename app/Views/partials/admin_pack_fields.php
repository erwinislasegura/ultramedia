<?php
$saved=!empty($photo['set_id'])?\App\Models\PhotoPack::forSet((int)$photo['set_id'],false):[];$bySlot=[];foreach($saved as $option)$bySlot[(int)$option['slot']]=$option;
$defaults=[1=>['quantity'=>5,'price'=>14990],2=>['quantity'=>10,'price'=>24990],3=>['quantity'=>15,'price'=>34990]];
?>
<div class="pack-config-three"><header><span><strong>PACKS POR CANTIDAD</strong><small>Configura hasta tres cantidades con precio fijo.</small></span><em>3 OPCIONES</em></header>
<?php foreach([1,2,3] as $slot):$option=array_merge($defaults[$slot],$bySlot[$slot]??[]);?><div class="pack-option-row"><label class="pack-check"><input type="checkbox" name="pack_active[<?=$slot?>]" <?=!empty($option['active'])?'checked':''?>><i></i><span>PACK <?=$slot?></span></label><label>CANTIDAD<input name="pack_quantity[<?=$slot?>]" type="number" min="2" value="<?=(int)$option['quantity']?>"></label><label>PRECIO<input name="pack_price[<?=$slot?>]" type="number" min="0" value="<?=(int)$option['price']?>"></label></div><?php endforeach;?>
</div>
