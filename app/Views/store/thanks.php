<section class="section narrow center"><span class="kicker dark">COMPRA CONFIRMADA</span><h1>¡TUS FOTOS ESTÁN LISTAS!</h1><p>Descarga los originales sin marca de agua.</p><?php foreach($items as $i):?><a class="download" href="<?=url('descarga?token='.urlencode($token).'&id='.$i['photo_id'])?>">↓ <?=htmlspecialchars($i['title'])?></a><?php endforeach;?></section>

