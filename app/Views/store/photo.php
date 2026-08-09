<?php if(!empty($_SESSION['error'])):?>
<div class="demo-note"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div>
<?php endif;?>

<div class="crumb">Inicio › Fotografías › <b><?=htmlspecialchars($photo['title'])?></b></div>

<section class="detail product-detail">
 <div class="detail-media">
  <div class="detail-stage">
   <div class="detail-img">
    <img id="detailMainImage" src="<?=preview_url($photo)?>" alt="<?=htmlspecialchars($photo['title'])?>">
    <span>ULTRA</span>
    <b>VISTA PREVIA PROTEGIDA</b>
    <div class="detail-photo-meta" aria-live="polite">
     <small id="detailMainPosition">FOTO 1 DE <?=count($related)?></small>
     <strong id="detailMainTitle"><?=htmlspecialchars($photo['title'])?></strong>
    </div>
   </div>
   <?php if(count($related)>1):?>
   <p class="detail-stage-help"><i></i>Pasa el cursor o toca una fotografía en la selección para verla en grande.</p>
   <?php endif;?>
  </div>
 </div>

 <div class="detail-copy">
  <span class="eyebrow dark">ARCHIVO DIGITAL · ALTA RESOLUCIÓN</span>
  <h1><?=htmlspecialchars($photo['set_name']?:$photo['title'])?></h1>
  <p><?=htmlspecialchars($photo['event_name'])?> · Competidor #<?=htmlspecialchars($photo['bib_number'])?></p>
  <ul>
   <li>✓ Selecciona una o varias fotografías</li>
   <li>✓ Originales sin marca de agua después del pago</li>
   <li>✓ También puedes comprar el set completo</li>
  </ul>

  <?php require ROOT.'/app/Views/partials/product_selection.php';?>

  <?php if($photo['set_id']&&!empty($photo['set_enabled'])):?>
  <div class="set-box detail-set-box">
   <span>MEJOR VALOR · TODO INCLUIDO</span>
   <h2>SET COMPLETO</h2>
   <p><?=count($related)?> fotografías originales en un único archivo ZIP.</p>
   <strong><?=money((int)$photo['set_price'])?></strong>
   <form method="post" action="<?=url('carrito/agregar')?>">
    <input type="hidden" name="_token" value="<?=csrf()?>">
    <input type="hidden" name="type" value="set">
    <input type="hidden" name="id" value="<?=$photo['set_id']?>">
    <button>COMPRAR SET COMPLETO →</button>
   </form>
  </div>
  <?php endif;?>
 </div>
</section>
